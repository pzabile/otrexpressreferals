import { redirect } from "next/navigation";
import { getCurrentAdmin } from "@/lib/auth";
import { LoginForm } from "./LoginForm";

export const metadata = { title: "Admin Login — OTR Express" };
export const dynamic = "force-dynamic";

export default async function AdminLoginPage() {
  const admin = await getCurrentAdmin();
  if (admin) redirect("/admin");
  return (
    <div className="mx-auto flex max-w-md flex-col px-6 py-16">
      <p className="text-xs font-semibold uppercase tracking-[0.3em] text-brand">
        Admin Access
      </p>
      <h1 className="mt-2 font-display text-4xl uppercase tracking-wide text-white">
        Sign In
      </h1>
      <p className="mt-2 text-sm text-slate-400">
        Use the admin credentials from your environment configuration.
      </p>
      <LoginForm />
    </div>
  );
}
