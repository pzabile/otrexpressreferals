import Link from "next/link";
import { prisma } from "@/lib/db";
import { StatusBadge } from "@/components/StatusBadge";
import { Timeline } from "@/components/Timeline";
import { formatDate, formatShortDate } from "@/lib/format";
import { statusDescription } from "@/lib/statuses";

export const metadata = { title: "Check Referral Status — OTR Express" };

type Search = { email?: string; phone?: string };

function digits(s: string | undefined) {
  return (s ?? "").replace(/\D+/g, "");
}

export default async function StatusPage({
  searchParams
}: {
  searchParams: Search;
}) {
  const email = (searchParams.email || "").trim().toLowerCase();
  const phoneDigits = digits(searchParams.phone);
  const hasQuery = email.length > 0 || phoneDigits.length > 0;

  let referrer: Awaited<
    ReturnType<typeof prisma.referrer.findFirst>
  > = null;
  let referrals: Awaited<ReturnType<typeof prisma.referral.findMany>> = [];

  if (hasQuery) {
    // Look up by email first, then fall back to phone (digits-only match).
    if (email) {
      referrer = await prisma.referrer.findUnique({ where: { email } });
    }
    if (!referrer && phoneDigits) {
      const all = await prisma.referrer.findMany();
      referrer =
        all.find((r) => digits(r.phone) === phoneDigits) ?? null;
    }
    if (referrer) {
      referrals = await prisma.referral.findMany({
        where: { referrerId: referrer.id },
        orderBy: { createdAt: "desc" },
        include: {
          comments: {
            where: { visibleToReferrer: true },
            orderBy: { createdAt: "desc" }
          },
          stageUpdates: { orderBy: { createdAt: "asc" } }
        }
      });
    }
  }

  return (
    <div className="mx-auto max-w-5xl px-6 py-14">
      <div className="mb-10">
        <p className="text-xs font-semibold uppercase tracking-[0.3em] text-brand">
          Referral Status
        </p>
        <h1 className="mt-2 font-display text-5xl uppercase tracking-wide text-white">
          Track Your Referrals
        </h1>
        <p className="mt-3 max-w-2xl text-slate-300">
          Enter the email or phone number you used when you referred the
          driver. All your referrals will show up with a full timeline and any
          admin notes.
        </p>
      </div>

      <form className="card grid gap-4 md:grid-cols-[1fr_1fr_auto]" method="get">
        <label className="field">
          <span className="field-label">Email used on referral</span>
          <input
            name="email"
            type="email"
            defaultValue={searchParams.email ?? ""}
            placeholder="you@example.com"
            className="field-input"
          />
        </label>
        <label className="field">
          <span className="field-label">Or phone number</span>
          <input
            name="phone"
            type="tel"
            defaultValue={searchParams.phone ?? ""}
            placeholder="(555) 123-4567"
            className="field-input"
          />
        </label>
        <div className="flex items-end">
          <button type="submit" className="btn-primary w-full md:w-auto">
            Look Up
          </button>
        </div>
      </form>

      {hasQuery && !referrer && (
        <div className="mt-10 rounded-md border border-rose-500/40 bg-rose-500/10 px-4 py-5 text-rose-200">
          No referrals found for that email or phone. Double-check what you
          entered, or{" "}
          <Link href="/refer" className="font-semibold underline">
            submit a new referral
          </Link>
          .
        </div>
      )}

      {referrer && referrals.length === 0 && (
        <div className="mt-10 rounded-md border border-ink-line bg-ink-soft px-4 py-5 text-slate-300">
          We found your account but no active referrals.{" "}
          <Link href="/refer" className="font-semibold text-brand">
            Refer a driver →
          </Link>
        </div>
      )}

      {referrals.length > 0 && (
        <div className="mt-10 space-y-8">
          {referrals.map((r) => (
            <article key={r.id} className="card">
              <div className="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div>
                  <p className="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">
                    Referral submitted {formatShortDate(r.createdAt)}
                  </p>
                  <h2 className="mt-1 font-display text-3xl uppercase tracking-wide text-white">
                    {r.driverName}
                  </h2>
                  <p className="mt-1 text-sm text-slate-400">
                    {r.driverEmail} &middot; {r.driverPhone}
                  </p>
                </div>
                <div className="flex flex-col items-start gap-2 md:items-end">
                  <StatusBadge status={r.status} />
                  <p className="max-w-xs text-right text-xs text-slate-400">
                    {statusDescription(r.status)}
                  </p>
                </div>
              </div>

              {r.status === "REJECTED" && r.rejectionReason && (
                <div className="mt-6 rounded-md border border-rose-500/40 bg-rose-500/10 p-4 text-sm text-rose-200">
                  <p className="font-semibold uppercase tracking-wider">
                    Reason for rejection
                  </p>
                  <p className="mt-1 text-rose-100">{r.rejectionReason}</p>
                </div>
              )}

              {r.startedWorkingAt && (
                <div className="mt-6 grid gap-3 rounded-md border border-ink-line bg-ink-soft/60 p-4 text-sm md:grid-cols-3">
                  <Stat
                    label="Started Working"
                    value={formatShortDate(r.startedWorkingAt)}
                  />
                  <Stat
                    label="14-Day Eligibility"
                    value={formatShortDate(r.payoutEligibleAt)}
                  />
                  <Stat
                    label="Paid Out"
                    value={r.paidAt ? formatShortDate(r.paidAt) : "Pending"}
                  />
                </div>
              )}

              <div className="mt-8 grid gap-8 md:grid-cols-2">
                <div>
                  <p className="mb-4 text-xs font-semibold uppercase tracking-[0.3em] text-brand">
                    Timeline
                  </p>
                  <Timeline
                    currentStatus={r.status}
                    updates={r.stageUpdates}
                  />
                </div>
                <div>
                  <p className="mb-4 text-xs font-semibold uppercase tracking-[0.3em] text-brand">
                    Admin Notes
                  </p>
                  {r.comments.length === 0 ? (
                    <p className="text-sm text-slate-500">
                      No notes yet. Check back after the admin reviews your
                      referral.
                    </p>
                  ) : (
                    <ul className="space-y-3">
                      {r.comments.map((c) => (
                        <li
                          key={c.id}
                          className="rounded-md border border-ink-line bg-ink-soft p-4"
                        >
                          <p className="text-xs uppercase tracking-wider text-slate-400">
                            {c.author === "SYSTEM" ? "System" : "Admin"} &middot;{" "}
                            {formatDate(c.createdAt)}
                          </p>
                          <p className="mt-1 whitespace-pre-wrap text-sm text-slate-200">
                            {c.body}
                          </p>
                        </li>
                      ))}
                    </ul>
                  )}
                </div>
              </div>
            </article>
          ))}
        </div>
      )}
    </div>
  );
}

function Stat({ label, value }: { label: string; value: string | null }) {
  return (
    <div>
      <p className="text-xs font-semibold uppercase tracking-wider text-slate-400">
        {label}
      </p>
      <p className="text-sm text-white">{value ?? "—"}</p>
    </div>
  );
}
