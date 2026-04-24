import Link from "next/link";
import { notFound, redirect } from "next/navigation";
import { getCurrentAdmin } from "@/lib/auth";
import { prisma } from "@/lib/db";
import { StatusBadge } from "@/components/StatusBadge";
import { Timeline } from "@/components/Timeline";
import { formatDate, formatShortDate } from "@/lib/format";
import { STATUS_ORDER, statusDescription, statusLabel } from "@/lib/statuses";
import {
  addCommentAction,
  markPaidAction,
  updateStatusAction
} from "../../actions";

export const dynamic = "force-dynamic";

export default async function AdminReferralPage({
  params
}: {
  params: { id: string };
}) {
  const admin = await getCurrentAdmin();
  if (!admin) redirect("/admin/login");

  const referral = await prisma.referral.findUnique({
    where: { id: params.id },
    include: {
      referrer: true,
      comments: { orderBy: { createdAt: "desc" } },
      stageUpdates: { orderBy: { createdAt: "asc" } }
    }
  });

  if (!referral) notFound();

  const isRejected = referral.status === "REJECTED";

  return (
    <div className="mx-auto max-w-6xl px-6 py-10">
      <Link
        href="/admin"
        className="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400 hover:text-brand"
      >
        ← Back to queue
      </Link>

      <div className="mt-3 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
          <p className="text-xs font-semibold uppercase tracking-[0.3em] text-brand">
            Referral
          </p>
          <h1 className="mt-1 font-display text-4xl uppercase tracking-wide text-white">
            {referral.driverName}
          </h1>
          <p className="mt-1 text-sm text-slate-400">
            Submitted {formatDate(referral.createdAt)} &middot; Last updated{" "}
            {formatDate(referral.updatedAt)}
          </p>
        </div>
        <div className="flex flex-col items-start gap-2 md:items-end">
          <StatusBadge status={referral.status} />
          <p className="max-w-xs text-right text-xs text-slate-400">
            {statusDescription(referral.status)}
          </p>
        </div>
      </div>

      <div className="mt-8 grid gap-6 lg:grid-cols-3">
        <div className="card lg:col-span-1">
          <h2 className="font-display text-xl uppercase tracking-wide text-white">
            Driver
          </h2>
          <dl className="mt-4 space-y-3 text-sm">
            <Row label="Name" value={referral.driverName} />
            <Row
              label="Email"
              value={
                <a
                  className="text-brand hover:underline"
                  href={`mailto:${referral.driverEmail}`}
                >
                  {referral.driverEmail}
                </a>
              }
            />
            <Row
              label="Phone"
              value={
                <a
                  className="text-brand hover:underline"
                  href={`tel:${referral.driverPhone.replace(/\D+/g, "")}`}
                >
                  {referral.driverPhone}
                </a>
              }
            />
          </dl>
          <div className="divider" />
          <h2 className="font-display text-xl uppercase tracking-wide text-white">
            Referrer
          </h2>
          <dl className="mt-4 space-y-3 text-sm">
            <Row label="Name" value={referral.referrer.name} />
            <Row
              label="Email"
              value={
                <a
                  className="text-brand hover:underline"
                  href={`mailto:${referral.referrer.email}`}
                >
                  {referral.referrer.email}
                </a>
              }
            />
            <Row
              label="Phone"
              value={
                <a
                  className="text-brand hover:underline"
                  href={`tel:${referral.referrer.phone.replace(/\D+/g, "")}`}
                >
                  {referral.referrer.phone}
                </a>
              }
            />
          </dl>
          <div className="divider" />
          <h2 className="font-display text-xl uppercase tracking-wide text-white">
            Payout
          </h2>
          <dl className="mt-4 space-y-3 text-sm">
            <Row
              label="Started Working"
              value={formatShortDate(referral.startedWorkingAt)}
            />
            <Row
              label="14-Day Eligibility"
              value={formatShortDate(referral.payoutEligibleAt)}
            />
            <Row
              label="Paid"
              value={
                referral.paidAt ? formatShortDate(referral.paidAt) : "Pending"
              }
            />
          </dl>
          {referral.status === "TWO_WEEKS_COMPLETED" && !referral.paidAt && (
            <form action={markPaidAction} className="mt-4">
              <input type="hidden" name="referralId" value={referral.id} />
              <button className="btn-primary w-full" type="submit">
                Mark Referrer Paid
              </button>
            </form>
          )}
        </div>

        <div className="space-y-6 lg:col-span-2">
          <section className="card">
            <h2 className="font-display text-xl uppercase tracking-wide text-white">
              Update Stage
            </h2>
            <p className="mt-1 text-sm text-slate-400">
              Moving to <span className="text-brand">Started Working</span>{" "}
              starts the 14-day clock automatically. Moving to{" "}
              <span className="text-brand">Rejected</span> requires a reason
              that the referrer will see.
            </p>
            <form action={updateStatusAction} className="mt-4 grid gap-4">
              <input type="hidden" name="referralId" value={referral.id} />
              <label className="field">
                <span className="field-label">New status</span>
                <select
                  name="status"
                  defaultValue={referral.status}
                  className="field-input"
                >
                  {STATUS_ORDER.map((s) => (
                    <option key={s} value={s}>
                      {statusLabel(s)}
                    </option>
                  ))}
                </select>
              </label>
              <label className="field">
                <span className="field-label">Internal note (optional)</span>
                <input
                  name="note"
                  placeholder="e.g. Sent application packet via email"
                  className="field-input"
                />
              </label>
              <label className="field">
                <span className="field-label">
                  Rejection reason (required if rejecting)
                </span>
                <textarea
                  name="rejectionReason"
                  defaultValue={referral.rejectionReason ?? ""}
                  className="field-textarea"
                  placeholder="Tell the referrer why this driver was rejected. This shows on their status page."
                />
              </label>
              <div className="flex items-center justify-between">
                <p className="text-xs text-slate-500">
                  This creates a timeline entry visible to the referrer.
                </p>
                <button type="submit" className="btn-primary">
                  Save Update
                </button>
              </div>
            </form>
          </section>

          <section className="card">
            <h2 className="font-display text-xl uppercase tracking-wide text-white">
              Timeline
            </h2>
            <div className="mt-6">
              <Timeline
                currentStatus={referral.status}
                updates={referral.stageUpdates}
              />
            </div>
            {isRejected && referral.rejectionReason && (
              <div className="mt-4 rounded-md border border-rose-500/40 bg-rose-500/10 p-4 text-sm text-rose-200">
                <p className="font-semibold uppercase tracking-wider">
                  Rejection reason shown to referrer
                </p>
                <p className="mt-1">{referral.rejectionReason}</p>
              </div>
            )}
          </section>

          <section className="card">
            <h2 className="font-display text-xl uppercase tracking-wide text-white">
              Comments
            </h2>
            <form action={addCommentAction} className="mt-4 space-y-3">
              <input type="hidden" name="referralId" value={referral.id} />
              <label className="field">
                <span className="field-label">New comment</span>
                <textarea
                  name="body"
                  required
                  className="field-textarea"
                  placeholder="Update the referrer, leave an internal note, anything."
                />
              </label>
              <label className="flex items-center gap-2 text-sm text-slate-300">
                <input
                  type="checkbox"
                  name="visibleToReferrer"
                  defaultChecked
                  className="h-4 w-4 rounded border-ink-line bg-ink-soft"
                />
                Visible to the referrer
              </label>
              <div className="flex justify-end">
                <button type="submit" className="btn-primary">
                  Post Comment
                </button>
              </div>
            </form>

            <ul className="mt-6 space-y-3">
              {referral.comments.length === 0 && (
                <li className="text-sm text-slate-500">
                  No comments yet. Leave one above.
                </li>
              )}
              {referral.comments.map((c) => (
                <li
                  key={c.id}
                  className="rounded-md border border-ink-line bg-ink-soft/80 p-4"
                >
                  <div className="flex items-center justify-between text-xs uppercase tracking-wider text-slate-400">
                    <span>
                      {c.author === "SYSTEM" ? "System" : "Admin"} &middot;{" "}
                      {formatDate(c.createdAt)}
                    </span>
                    <span
                      className={
                        c.visibleToReferrer
                          ? "text-emerald-300"
                          : "text-slate-500"
                      }
                    >
                      {c.visibleToReferrer ? "Visible to referrer" : "Internal"}
                    </span>
                  </div>
                  <p className="mt-2 whitespace-pre-wrap text-sm text-slate-100">
                    {c.body}
                  </p>
                </li>
              ))}
            </ul>
          </section>
        </div>
      </div>
    </div>
  );
}

function Row({
  label,
  value
}: {
  label: string;
  value: React.ReactNode;
}) {
  return (
    <div className="flex items-center justify-between gap-4">
      <dt className="text-xs font-semibold uppercase tracking-wider text-slate-400">
        {label}
      </dt>
      <dd className="text-right text-sm text-white">{value || "—"}</dd>
    </div>
  );
}
