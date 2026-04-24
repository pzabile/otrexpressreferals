export const STATUS_ORDER = [
  "SUBMITTED",
  "CONTACTED",
  "APPLICATION_SENT",
  "WAITING_ON_DOCUMENTS",
  "WAITING_ON_INSURANCE",
  "ORIENTATION_SCHEDULED",
  "STARTED_WORKING",
  "TWO_WEEKS_COMPLETED",
  "HIRED_PAID",
  "REJECTED"
] as const;

export type ReferralStatus = (typeof STATUS_ORDER)[number];

export const STATUS_LABELS: Record<ReferralStatus, string> = {
  SUBMITTED: "Referral Submitted",
  CONTACTED: "Driver Contacted",
  APPLICATION_SENT: "Application Sent",
  WAITING_ON_DOCUMENTS: "Waiting on Documents",
  WAITING_ON_INSURANCE: "Waiting on Insurance",
  ORIENTATION_SCHEDULED: "Orientation Scheduled",
  STARTED_WORKING: "Started Working",
  TWO_WEEKS_COMPLETED: "14 Days Completed — Payout Eligible",
  HIRED_PAID: "Hired & Referrer Paid",
  REJECTED: "Rejected"
};

export const STATUS_DESCRIPTIONS: Record<ReferralStatus, string> = {
  SUBMITTED: "We received your referral and it's in the queue.",
  CONTACTED: "Our recruiter has reached out to the driver.",
  APPLICATION_SENT: "Driver was sent the application packet.",
  WAITING_ON_DOCUMENTS: "Waiting on CDL, MVR, medical card, or other documents.",
  WAITING_ON_INSURANCE: "Under insurance review.",
  ORIENTATION_SCHEDULED: "Driver is scheduled to attend orientation.",
  STARTED_WORKING: "Driver started working. 14-day clock is running.",
  TWO_WEEKS_COMPLETED: "Driver completed 14 days. Referrer payout approved.",
  HIRED_PAID: "Driver is hired and referrer has been paid.",
  REJECTED: "Referral was not accepted. See the reason provided."
};

export const STATUS_COLORS: Record<ReferralStatus, string> = {
  SUBMITTED: "bg-sky-500/15 text-sky-300 ring-sky-400/30",
  CONTACTED: "bg-indigo-500/15 text-indigo-300 ring-indigo-400/30",
  APPLICATION_SENT: "bg-violet-500/15 text-violet-300 ring-violet-400/30",
  WAITING_ON_DOCUMENTS: "bg-amber-500/15 text-amber-300 ring-amber-400/30",
  WAITING_ON_INSURANCE: "bg-amber-500/15 text-amber-300 ring-amber-400/30",
  ORIENTATION_SCHEDULED: "bg-cyan-500/15 text-cyan-300 ring-cyan-400/30",
  STARTED_WORKING: "bg-emerald-500/15 text-emerald-300 ring-emerald-400/30",
  TWO_WEEKS_COMPLETED: "bg-emerald-500/20 text-emerald-300 ring-emerald-400/40",
  HIRED_PAID: "bg-brand/20 text-brand-light ring-brand/40",
  REJECTED: "bg-rose-500/15 text-rose-300 ring-rose-400/30"
};

export function isTerminalStatus(status: string) {
  return status === "HIRED_PAID" || status === "REJECTED";
}

export function statusLabel(status: string) {
  return (STATUS_LABELS as Record<string, string>)[status] ?? status;
}

export function statusDescription(status: string) {
  return (STATUS_DESCRIPTIONS as Record<string, string>)[status] ?? "";
}

export function statusColor(status: string) {
  return (
    (STATUS_COLORS as Record<string, string>)[status] ??
    "bg-slate-500/15 text-slate-300 ring-slate-400/30"
  );
}
