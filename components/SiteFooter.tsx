export function SiteFooter() {
  return (
    <footer className="border-t border-ink-line/60 bg-ink">
      <div className="mx-auto flex max-w-6xl flex-col gap-4 px-6 py-8 text-sm text-slate-400 md:flex-row md:items-center md:justify-between">
        <div>
          <p className="font-display text-lg uppercase tracking-widest text-white">
            OTR Express
          </p>
          <p className="text-xs uppercase tracking-[0.25em] text-brand">
            Driver Referrals Program
          </p>
        </div>
        <div className="flex flex-col gap-1 md:items-end">
          <p>
            &copy; {new Date().getFullYear()} OTR Express Group. All rights
            reserved.
          </p>
          <p className="text-xs">
            Refer. Track. Get paid when your driver runs 14 days.
          </p>
        </div>
      </div>
    </footer>
  );
}
