"use client";

import { useFormState, useFormStatus } from "react-dom";
import { loginAction, type LoginState } from "../actions";

const initialState: LoginState = {};

export function LoginForm() {
  const [state, action] = useFormState(loginAction, initialState);
  return (
    <form action={action} className="card mt-8 space-y-4">
      <label className="field">
        <span className="field-label">Email</span>
        <input
          name="email"
          type="email"
          required
          className="field-input"
          autoComplete="username"
        />
      </label>
      <label className="field">
        <span className="field-label">Password</span>
        <input
          name="password"
          type="password"
          required
          className="field-input"
          autoComplete="current-password"
        />
      </label>
      {state.error && (
        <p className="rounded-md border border-rose-500/40 bg-rose-500/10 px-3 py-2 text-sm text-rose-200">
          {state.error}
        </p>
      )}
      <SubmitButton />
    </form>
  );
}

function SubmitButton() {
  const { pending } = useFormStatus();
  return (
    <button className="btn-primary w-full" type="submit" disabled={pending}>
      {pending ? "Signing in…" : "Sign In"}
    </button>
  );
}
