import { ReferralForm } from "./ReferralForm";

export const metadata = {
  title: "Refer a Driver — OTR Express"
};

export default function ReferPage() {
  const bounty = process.env.NEXT_PUBLIC_REFERRAL_BOUNTY || "500";
  return (
    <div className="mx-auto max-w-4xl px-6 py-14">
      <div className="mb-10">
        <p className="text-xs font-semibold uppercase tracking-[0.3em] text-brand">
          Refer a Driver
        </p>
        <h1 className="mt-2 font-display text-5xl uppercase tracking-wide text-white">
          Submit Your Referral
        </h1>
        <p className="mt-3 max-w-2xl text-slate-300">
          Tell us who you&apos;re referring and how we can reach you. Your
          submission goes straight to the admin queue. You&apos;ll earn $
          {bounty} once the driver completes 14 days.
        </p>
      </div>
      <ReferralForm />
    </div>
  );
}
