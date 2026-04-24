import Link from "next/link";
import { getCurrentAdmin } from "@/lib/auth";
import { logoutAction } from "./actions";

export const dynamic = "force-dynamic";

export default async function AdminLayout({
  children
}: {
  children: React.ReactNode;
}) {
  const admin = await getCurrentAdmin();
  return (
    <div className="min-h-[calc(100vh-64px)] bg-ink">
      <div className="border-b border-ink-line/70 bg-ink-soft">
        <div className="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
          <div className="flex items-center gap-3">
            <span className="rounded-md bg-brand/20 px-3 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-brand">
              Admin Console
            </span>
            {admin && (
              <nav className="hidden gap-2 md:flex">
                <Link
                  href="/admin"
                  className="rounded px-3 py-1.5 text-sm font-semibold uppercase tracking-wider text-slate-300 hover:text-white"
                >
                  Referrals
                </Link>
              </nav>
            )}
          </div>
          {admin && (
            <form action={logoutAction} className="flex items-center gap-3">
              <span className="hidden text-xs text-slate-400 md:inline">
                {admin.email}
              </span>
              <button
                type="submit"
                className="rounded-md border border-ink-line px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-slate-300 hover:border-rose-500 hover:text-rose-300"
              >
                Sign Out
              </button>
            </form>
          )}
        </div>
      </div>
      <div>{children}</div>
    </div>
  );
}
