"use client";

import { useFormState, useFormStatus } from "react-dom";
import { submitReferral, type ReferralFormState } from "./actions";

const initialState: ReferralFormState = {};

export function ReferralForm() {
  const [state, formAction] = useFormState(submitReferral, initialState);
  const err = (k: string) => state.fieldErrors?.[k];

  return (
    <form action={formAction} className="grid gap-8">
      <section className="card">
        <h2 className="font-display text-2xl uppercase tracking-wide text-white">
          Your Info (Referrer)
        </h2>
        <p className="mt-1 text-sm text-slate-400">
          We need this so we can pay you and send you status updates.
        </p>
        <div className="mt-6 grid gap-4 md:grid-cols-2">
          <Field
            name="referrerName"
            label="Your full name"
            placeholder="John Doe"
            error={err("referrerName")}
            required
          />
          <Field
            name="referrerPhone"
            label="Your phone"
            placeholder="(555) 123-4567"
            type="tel"
            error={err("referrerPhone")}
            required
          />
          <div className="md:col-span-2">
            <Field
              name="referrerEmail"
              label="Your email"
              placeholder="you@example.com"
              type="email"
              error={err("referrerEmail")}
              required
            />
          </div>
        </div>
      </section>

      <section className="card">
        <h2 className="font-display text-2xl uppercase tracking-wide text-white">
          Driver You&apos;re Referring
        </h2>
        <p className="mt-1 text-sm text-slate-400">
          Give us the driver&apos;s full legal name, email, and best phone
          number. We&apos;ll reach out directly.
        </p>
        <div className="mt-6 grid gap-4 md:grid-cols-2">
          <Field
            name="driverName"
            label="Driver full name"
            placeholder="Jane Smith"
            error={err("driverName")}
            required
          />
          <Field
            name="driverPhone"
            label="Driver phone"
            placeholder="(555) 987-6543"
            type="tel"
            error={err("driverPhone")}
            required
          />
          <div className="md:col-span-2">
            <Field
              name="driverEmail"
              label="Driver email"
              placeholder="driver@example.com"
              type="email"
              error={err("driverEmail")}
              required
            />
          </div>
          <div className="md:col-span-2">
            <label className="field">
              <span className="field-label">Notes (optional)</span>
              <textarea
                name="notes"
                className="field-textarea"
                placeholder="CDL class, years of experience, best time to call, anything we should know…"
              />
            </label>
          </div>
        </div>
      </section>

      {state.error && (
        <div className="rounded-md border border-rose-500/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
          {state.error}
        </div>
      )}

      <div className="flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p className="text-xs text-slate-500">
          By submitting you confirm the driver has agreed to be contacted by
          OTR Express.
        </p>
        <SubmitButton />
      </div>
    </form>
  );
}

function Field({
  name,
  label,
  placeholder,
  type = "text",
  required,
  error
}: {
  name: string;
  label: string;
  placeholder?: string;
  type?: string;
  required?: boolean;
  error?: string;
}) {
  return (
    <label className="field">
      <span className="field-label">
        {label}
        {required ? " *" : ""}
      </span>
      <input
        name={name}
        type={type}
        required={required}
        placeholder={placeholder}
        className="field-input"
        autoComplete="off"
      />
      {error && <span className="text-xs text-rose-300">{error}</span>}
    </label>
  );
}

function SubmitButton() {
  const { pending } = useFormStatus();
  return (
    <button type="submit" className="btn-primary" disabled={pending}>
      {pending ? "Submitting…" : "Submit Referral"}
    </button>
  );
}
