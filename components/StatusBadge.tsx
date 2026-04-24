import { statusColor, statusLabel } from "@/lib/statuses";

export function StatusBadge({ status }: { status: string }) {
  return (
    <span className={`chip ${statusColor(status)}`}>
      <span className="h-1.5 w-1.5 rounded-full bg-current" />
      {statusLabel(status)}
    </span>
  );
}
