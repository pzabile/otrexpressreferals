import Link from "next/link";

export function SiteNav() {
  return (
    <header className="sticky top-0 z-40 border-b border-ink-line/70 bg-ink/90 backdrop-blur">
      <div className="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
        <Link href="/" className="flex items-center gap-3">
          <span className="flex h-10 w-10 items-center justify-center rounded-md bg-brand text-black shadow-lg shadow-brand/30">
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2.5"
              strokeLinecap="round"
              strokeLinejoin="round"
              className="h-6 w-6"
              aria-hidden
            >
              <path d="M3 7h11v8H3z" />
              <path d="M14 10h4l3 3v2h-7z" />
              <circle cx="7" cy="17" r="2" />
              <circle cx="17" cy="17" r="2" />
            </svg>
          </span>
          <span className="flex flex-col leading-tight">
            <span className="font-display text-xl uppercase tracking-widest text-white">
              OTR Express
            </span>
            <span className="text-[11px] font-semibold uppercase tracking-[0.25em] text-brand">
              Driver Referrals
            </span>
          </span>
        </Link>
        <nav className="flex items-center gap-1 text-sm font-semibold uppercase tracking-wider">
          <Link
            href="/"
            className="rounded px-3 py-2 text-slate-300 hover:text-white"
          >
            Home
          </Link>
          <Link
            href="/refer"
            className="rounded px-3 py-2 text-slate-300 hover:text-white"
          >
            Refer a Driver
          </Link>
          <Link
            href="/status"
            className="rounded px-3 py-2 text-slate-300 hover:text-white"
          >
            Check Status
          </Link>
          <Link
            href="/admin"
            className="ml-2 rounded-md border border-ink-line px-3 py-2 text-slate-300 hover:border-brand hover:text-brand"
          >
            Admin
          </Link>
        </nav>
      </div>
    </header>
  );
}
