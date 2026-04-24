import Link from "next/link";
import { STATUS_ORDER, statusDescription, statusLabel } from "@/lib/statuses";

export default function HomePage() {
  const bounty = process.env.NEXT_PUBLIC_REFERRAL_BOUNTY || "500";
  const steps = STATUS_ORDER.filter((s) => s !== "REJECTED");

  return (
    <div>
      <section className="relative overflow-hidden bg-hero-grid">
        <div className="mx-auto grid max-w-6xl gap-10 px-6 py-20 md:grid-cols-2 md:items-center md:py-28">
          <div>
            <p className="mb-4 inline-block rounded-full border border-brand/40 bg-brand/10 px-4 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-brand">
              Driver Referral Program
            </p>
            <h1 className="font-display text-5xl uppercase leading-[0.95] tracking-tight text-white md:text-7xl">
              Refer a Driver.
              <span className="block text-brand">Get Paid.</span>
            </h1>
            <p className="mt-6 max-w-xl text-lg text-slate-300">
              Know a solid CDL driver? Send them our way. We handle recruiting,
              onboarding, and the paperwork. You earn{" "}
              <span className="font-semibold text-brand">
                ${bounty}
              </span>{" "}
              per driver that completes 14 days on the road with OTR Express.
            </p>
            <div className="mt-8 flex flex-col gap-3 sm:flex-row">
              <Link href="/refer" className="btn-primary">
                Refer a Driver
              </Link>
              <Link href="/status" className="btn-ghost">
                Check Referral Status
              </Link>
            </div>
          </div>
          <div className="relative">
            <div className="card border-brand/30 bg-ink-soft/90">
              <p className="text-xs font-semibold uppercase tracking-[0.3em] text-brand">
                How It Works
              </p>
              <ol className="mt-4 space-y-4">
                <li className="flex gap-4">
                  <span className="flex h-8 w-8 flex-none items-center justify-center rounded-full bg-brand text-sm font-bold text-black">
                    1
                  </span>
                  <div>
                    <p className="font-semibold text-white">Submit the driver</p>
                    <p className="text-sm text-slate-400">
                      Name, email, phone. Your contact info too so we can pay
                      you.
                    </p>
                  </div>
                </li>
                <li className="flex gap-4">
                  <span className="flex h-8 w-8 flex-none items-center justify-center rounded-full bg-brand text-sm font-bold text-black">
                    2
                  </span>
                  <div>
                    <p className="font-semibold text-white">Track every step</p>
                    <p className="text-sm text-slate-400">
                      Contacted, application, documents, insurance, orientation
                      — all visible in your status page.
                    </p>
                  </div>
                </li>
                <li className="flex gap-4">
                  <span className="flex h-8 w-8 flex-none items-center justify-center rounded-full bg-brand text-sm font-bold text-black">
                    3
                  </span>
                  <div>
                    <p className="font-semibold text-white">
                      Get paid at 14 days
                    </p>
                    <p className="text-sm text-slate-400">
                      Once the driver completes 14 days working, your ${bounty}{" "}
                      payout is approved.
                    </p>
                  </div>
                </li>
              </ol>
            </div>
          </div>
        </div>
      </section>

      <section className="border-y border-ink-line/70 bg-ink">
        <div className="mx-auto max-w-6xl px-6 py-16">
          <div className="flex flex-col items-start justify-between gap-6 md:flex-row md:items-end">
            <div>
              <p className="text-xs font-semibold uppercase tracking-[0.3em] text-brand">
                Full Visibility
              </p>
              <h2 className="mt-2 font-display text-4xl uppercase tracking-wide text-white">
                See Every Stage of the Hire
              </h2>
              <p className="mt-3 max-w-2xl text-slate-400">
                Unlike most referral programs, you don&apos;t just send a name
                and hope. You see exactly where your driver is in our pipeline
                and read admin notes in real time.
              </p>
            </div>
            <Link href="/status" className="btn-ghost">
              Open Status Tracker
            </Link>
          </div>
          <div className="mt-10 grid gap-4 md:grid-cols-3">
            {steps.map((s) => (
              <div
                key={s}
                className="rounded-lg border border-ink-line bg-ink-soft/70 p-5"
              >
                <p className="text-sm font-semibold uppercase tracking-wider text-brand">
                  {statusLabel(s)}
                </p>
                <p className="mt-2 text-sm text-slate-400">
                  {statusDescription(s)}
                </p>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="mx-auto max-w-6xl px-6 py-20">
        <div className="grid gap-8 md:grid-cols-2">
          <div className="card">
            <p className="text-xs font-semibold uppercase tracking-[0.3em] text-brand">
              The Rules
            </p>
            <h3 className="mt-2 font-display text-3xl uppercase tracking-wide text-white">
              Simple, Fair, Transparent
            </h3>
            <ul className="mt-4 space-y-3 text-sm text-slate-300">
              <li className="flex gap-3">
                <Check />
                Submit the driver through the referral form with their name,
                email, and phone.
              </li>
              <li className="flex gap-3">
                <Check />
                Admin reviews and moves the referral through each stage:
                contacted, documents, insurance, orientation.
              </li>
              <li className="flex gap-3">
                <Check />
                If the driver is rejected, you see the reason in your status
                page.
              </li>
              <li className="flex gap-3">
                <Check />
                When the driver starts, the 14-day clock begins. On day 14, the
                referral is marked payout-eligible.
              </li>
              <li className="flex gap-3">
                <Check />
                Payout is released once the admin confirms and marks the
                referral paid.
              </li>
            </ul>
          </div>
          <div className="card border-brand/40">
            <p className="text-xs font-semibold uppercase tracking-[0.3em] text-brand">
              Ready?
            </p>
            <h3 className="mt-2 font-display text-3xl uppercase tracking-wide text-white">
              Send Us Your Next Driver
            </h3>
            <p className="mt-3 text-slate-300">
              It takes less than a minute. You&apos;ll get a confirmation and a
              status link you can check anytime with your email and phone.
            </p>
            <div className="mt-6 flex flex-col gap-3 sm:flex-row">
              <Link href="/refer" className="btn-primary">
                Start a Referral
              </Link>
              <Link href="/status" className="btn-ghost">
                I already referred someone
              </Link>
            </div>
          </div>
        </div>
      </section>
    </div>
  );
}

function Check() {
  return (
    <svg
      viewBox="0 0 20 20"
      fill="currentColor"
      className="mt-0.5 h-4 w-4 flex-none text-brand"
      aria-hidden
    >
      <path
        fillRule="evenodd"
        d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.25 7.31a1 1 0 0 1-1.42.003l-3.75-3.75a1 1 0 1 1 1.414-1.414l3.04 3.04 6.546-6.597a1 1 0 0 1 1.414-.006z"
        clipRule="evenodd"
      />
    </svg>
  );
}
