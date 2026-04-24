import { STATUS_ORDER, statusLabel } from "@/lib/statuses";
import { formatDate } from "@/lib/format";

type TimelineItem = {
  status: string;
  note?: string | null;
  createdAt: Date | string;
};

export function Timeline({
  currentStatus,
  updates
}: {
  currentStatus: string;
  updates: TimelineItem[];
}) {
  const isRejected = currentStatus === "REJECTED";
  const currentIndex = STATUS_ORDER.indexOf(currentStatus as never);
  const ordered = STATUS_ORDER.filter((s) => s !== "REJECTED");
  const byStatus = new Map<string, TimelineItem>();
  for (const u of updates) {
    if (!byStatus.has(u.status)) byStatus.set(u.status, u);
  }

  return (
    <ol className="relative border-l border-ink-line/80 pl-6">
      {ordered.map((step, i) => {
        const reached = !isRejected && currentIndex >= i;
        const active = !isRejected && currentIndex === i;
        const update = byStatus.get(step);
        return (
          <li key={step} className="mb-6 last:mb-0">
            <span
              className={`absolute -left-[9px] flex h-4 w-4 items-center justify-center rounded-full ring-4 ring-ink ${
                reached
                  ? active
                    ? "bg-brand"
                    : "bg-emerald-500"
                  : "bg-ink-line"
              }`}
            />
            <div className="flex flex-col">
              <span
                className={`text-sm font-semibold uppercase tracking-wider ${
                  reached ? "text-white" : "text-slate-500"
                }`}
              >
                {statusLabel(step)}
              </span>
              {update && (
                <span className="text-xs text-slate-400">
                  {formatDate(update.createdAt)}
                  {update.note ? ` — ${update.note}` : ""}
                </span>
              )}
            </div>
          </li>
        );
      })}
      {isRejected && (
        <li className="mt-2">
          <span className="absolute -left-[9px] flex h-4 w-4 items-center justify-center rounded-full bg-rose-500 ring-4 ring-ink" />
          <span className="text-sm font-semibold uppercase tracking-wider text-rose-300">
            Rejected
          </span>
        </li>
      )}
    </ol>
  );
}
