import Link from "next/link";
import { redirect } from "next/navigation";
import { getCurrentAdmin } from "@/lib/auth";
import { prisma } from "@/lib/db";
import { StatusBadge } from "@/components/StatusBadge";
import { formatShortDate } from "@/lib/format";
import { STATUS_ORDER, statusLabel } from "@/lib/statuses";

export const metadata = { title: "Admin Dashboard — OTR Express" };
export const dynamic = "force-dynamic";

type Search = { status?: string; q?: string };

export default async function AdminDashboard({
  searchParams
}: {
  searchParams: Search;
}) {
  const admin = await getCurrentAdmin();
  if (!admin) redirect("/admin/login");

  const filterStatus = searchParams.status?.trim() || "";
  const query = (searchParams.q ?? "").trim();

  const where: Record<string, unknown> = {};
  if (filterStatus && STATUS_ORDER.includes(filterStatus as never)) {
    where.status = filterStatus;
  }
  if (query) {
    where.OR = [
      { driverName: { contains: query } },
      { driverEmail: { contains: query } },
      { driverPhone: { contains: query } },
      { referrer: { name: { contains: query } } },
      { referrer: { email: { contains: query } } },
      { referrer: { phone: { contains: query } } }
    ];
  }

  const referrals = await prisma.referral.findMany({
    where,
    orderBy: { createdAt: "desc" },
    include: {
      referrer: true,
      _count: { select: { comments: true } }
    }
  });

  const counts = await prisma.referral.groupBy({
    by: ["status"],
    _count: { _all: true }
  });
  const countMap = new Map(counts.map((c) => [c.status, c._count._all]));
  const total = referrals.length;

  return (
    <div className="mx-auto max-w-6xl px-6 py-10">
      <div className="flex flex-col items-start justify-between gap-4 md:flex-row md:items-end">
        <div>
          <p className="text-xs font-semibold uppercase tracking-[0.3em] text-brand">
            Referrals Queue
          </p>
          <h1 className="mt-1 font-display text-4xl uppercase tracking-wide text-white">
            All Driver Referrals
          </h1>
          <p className="mt-1 text-sm text-slate-400">
            {total} shown{filterStatus ? ` — filtered by ${statusLabel(filterStatus)}` : ""}
          </p>
        </div>
        <form className="flex flex-col gap-2 sm:flex-row" method="get">
          <input
            name="q"
            defaultValue={query}
            placeholder="Search driver or referrer…"
            className="field-input w-full sm:w-72"
          />
          <select
            name="status"
            defaultValue={filterStatus}
            className="field-input sm:w-56"
          >
            <option value="">All statuses</option>
            {STATUS_ORDER.map((s) => (
              <option key={s} value={s}>
                {statusLabel(s)}
              </option>
            ))}
          </select>
          <button type="submit" className="btn-primary">
            Filter
          </button>
        </form>
      </div>

      <div className="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
        <StatCard
          label="Total"
          value={Array.from(countMap.values()).reduce((a, b) => a + b, 0)}
        />
        <StatCard label="New" value={countMap.get("SUBMITTED") ?? 0} />
        <StatCard
          label="In Progress"
          value={
            (countMap.get("CONTACTED") ?? 0) +
            (countMap.get("APPLICATION_SENT") ?? 0) +
            (countMap.get("WAITING_ON_DOCUMENTS") ?? 0) +
            (countMap.get("WAITING_ON_INSURANCE") ?? 0) +
            (countMap.get("ORIENTATION_SCHEDULED") ?? 0) +
            (countMap.get("STARTED_WORKING") ?? 0)
          }
        />
        <StatCard
          label="Payout Ready"
          value={countMap.get("TWO_WEEKS_COMPLETED") ?? 0}
          accent
        />
        <StatCard label="Hired" value={countMap.get("HIRED_PAID") ?? 0} />
      </div>

      <div className="mt-8 overflow-x-auto rounded-xl border border-ink-line bg-ink-soft/60">
        <table className="min-w-full divide-y divide-ink-line text-left text-sm">
          <thead className="bg-ink-soft text-xs uppercase tracking-wider text-slate-400">
            <tr>
              <th className="px-4 py-3">Driver</th>
              <th className="px-4 py-3">Referrer</th>
              <th className="px-4 py-3">Status</th>
              <th className="px-4 py-3">Submitted</th>
              <th className="px-4 py-3">Notes</th>
              <th className="px-4 py-3"></th>
            </tr>
          </thead>
          <tbody className="divide-y divide-ink-line">
            {referrals.length === 0 && (
              <tr>
                <td
                  colSpan={6}
                  className="px-4 py-10 text-center text-slate-500"
                >
                  No referrals match this filter yet.
                </td>
              </tr>
            )}
            {referrals.map((r) => (
              <tr
                key={r.id}
                className="transition hover:bg-ink-mid/40"
              >
                <td className="px-4 py-4 align-top">
                  <p className="font-semibold text-white">{r.driverName}</p>
                  <p className="text-xs text-slate-400">{r.driverEmail}</p>
                  <p className="text-xs text-slate-400">{r.driverPhone}</p>
                </td>
                <td className="px-4 py-4 align-top">
                  <p className="text-white">{r.referrer.name}</p>
                  <p className="text-xs text-slate-400">{r.referrer.email}</p>
                  <p className="text-xs text-slate-400">{r.referrer.phone}</p>
                </td>
                <td className="px-4 py-4 align-top">
                  <StatusBadge status={r.status} />
                </td>
                <td className="px-4 py-4 align-top text-xs text-slate-300">
                  {formatShortDate(r.createdAt)}
                </td>
                <td className="px-4 py-4 align-top text-xs text-slate-300">
                  {r._count.comments} note
                  {r._count.comments === 1 ? "" : "s"}
                </td>
                <td className="px-4 py-4 text-right align-top">
                  <Link
                    href={`/admin/referrals/${r.id}`}
                    className="inline-block rounded-md border border-ink-line px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-slate-200 hover:border-brand hover:text-brand"
                  >
                    Open
                  </Link>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}

function StatCard({
  label,
  value,
  accent
}: {
  label: string;
  value: number;
  accent?: boolean;
}) {
  return (
    <div
      className={`rounded-lg border p-4 ${
        accent
          ? "border-brand/50 bg-brand/10"
          : "border-ink-line bg-ink-soft"
      }`}
    >
      <p className="text-xs font-semibold uppercase tracking-wider text-slate-400">
        {label}
      </p>
      <p
        className={`mt-1 text-3xl font-bold ${
          accent ? "text-brand" : "text-white"
        }`}
      >
        {value}
      </p>
    </div>
  );
}
