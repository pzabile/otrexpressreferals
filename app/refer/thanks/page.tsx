import Link from "next/link";
import { prisma } from "@/lib/db";

export const metadata = { title: "Referral Received — OTR Express" };

export default async function ThanksPage({
  searchParams
}: {
  searchParams: { id?: string };
}) {
  const referral = searchParams.id
    ? await prisma.referral.findUnique({
        where: { id: searchParams.id },
        include: { referrer: true }
      })
    : null;

  return (
    <div className="mx-auto max-w-3xl px-6 py-20 text-center">
      <p className="text-xs font-semibold uppercase tracking-[0.3em] text-brand">
        Referral Received
      </p>
      <h1 className="mt-3 font-display text-5xl uppercase tracking-wide text-white">
        You&apos;re in the queue.
      </h1>
      <p className="mx-auto mt-4 max-w-xl text-slate-300">
        Thanks{referral?.referrer?.name ? `, ${referral.referrer.name}` : ""}.
        Your referral has been sent to the OTR Express admin team. Check back
        anytime to see where {referral?.driverName || "your driver"} is in the
        pipeline.
      </p>
      <div className="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
        <Link href="/status" className="btn-primary">
          Check Referral Status
        </Link>
        <Link href="/refer" className="btn-ghost">
          Refer Another Driver
        </Link>
      </div>
    </div>
  );
}
