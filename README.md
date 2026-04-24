# OTR Express — Driver Referral Program

A full-stack web app where drivers and fleet contacts can refer new CDL drivers
to OTR Express, track every step of the hiring pipeline, and earn a payout
when the referred driver completes 14 days on the road.

Built with Next.js 14 (App Router), Prisma, SQLite (swap to Postgres for
production), and Tailwind CSS. Visual design is inspired by
otrexpressgroup.com — dark industrial theme with orange accents, heavy
display typography, and emphasis on trucking imagery.

## Features

- **Public landing page** explaining the program, payout rules, and pipeline.
- **Refer a Driver** form capturing referrer + driver name, email, and phone.
- **Referrer status page** — no account needed. Enter the email or phone used
  on the referral to see every referral's timeline, admin notes, and
  rejection reasons.
- **Admin console** (protected by email + password):
  - Queue view with filters, search, and pipeline KPIs.
  - Referral detail page with timeline, stage updates, and comments.
  - Every status change writes a timeline entry visible to the referrer.
  - Moving a driver to **Started Working** starts the 14-day clock
    automatically and computes the payout-eligible date.
  - Marking **Rejected** requires a rejection reason, which is shown to the
    referrer.
  - One-click **Mark Referrer Paid** when the 14-day clock is done.
- **Pipeline stages**: Submitted → Contacted → Application Sent →
  Waiting on Documents → Waiting on Insurance → Orientation Scheduled →
  Started Working → 14 Days Completed → Hired & Paid. Plus a terminal
  **Rejected** state.

## Deploying to Hostinger

See [HOSTINGER.md](./HOSTINGER.md) for step-by-step instructions covering
both a Hostinger VPS and a Hostinger Premium/Business/Cloud plan with
the Node.js app selector. (Basic PHP-only shared hosting cannot run this
app.)

## Local development

```bash
# 1. Install deps
npm install

# 2. Copy env template and edit values
cp .env.example .env
# Set ADMIN_EMAIL, ADMIN_PASSWORD, SESSION_SECRET (32+ chars)

# 3. Create the SQLite database and tables
npx prisma db push

# 4. Run the dev server
npm run dev
```

Then open:

- `http://localhost:3000/` — public landing page
- `http://localhost:3000/refer` — referral form
- `http://localhost:3000/status` — referrer status lookup
- `http://localhost:3000/admin` — admin console (redirects to login)

The admin user is auto-provisioned on first login using `ADMIN_EMAIL` /
`ADMIN_PASSWORD` from your `.env`.

## Production notes

- SQLite is fine for a single-instance deployment but has no multi-writer
  story. For production, change `prisma/schema.prisma` provider to
  `postgresql` and set `DATABASE_URL` to a managed Postgres (Neon, Supabase,
  RDS, Railway, etc.), then run `npx prisma migrate deploy`.
- Set `SESSION_SECRET` to a long random string. The admin session cookie is
  HMAC-signed with this secret.
- Set `NEXT_PUBLIC_SITE_URL` to your public URL for correct links.
- `NEXT_PUBLIC_REFERRAL_BOUNTY` controls the payout amount shown on the
  public site (purely display).

### Deploying to Vercel

1. Push the repo to GitHub.
2. Import into Vercel.
3. Add the environment variables from `.env.example`.
4. Use a managed Postgres (not SQLite) and set `DATABASE_URL` accordingly.
5. The build script runs `prisma generate && prisma migrate deploy && next build`.

## Project layout

```
app/
  layout.tsx                   # Site shell (nav + footer)
  page.tsx                     # Public landing page
  refer/                       # Referral submission + thanks
  status/                      # Referrer status lookup
  admin/                       # Admin auth, dashboard, detail
components/                    # SiteNav, SiteFooter, StatusBadge, Timeline
lib/
  db.ts                        # Prisma client singleton
  auth.ts                      # Admin auth (bcrypt + signed cookie)
  statuses.ts                  # Status enum, labels, colors
  format.ts                    # Date/phone formatting
prisma/schema.prisma           # AdminUser, Referrer, Referral, Comment, StageUpdate
```

## Rules recap (matches the requirements)

- Drivers are referred via the public form with **name, email, and phone**.
- All referrals land in the **admin queue**.
- Admin can **add comments** (visible to referrer or internal-only) and
  **move the referral through every stage**: contacted, waiting on documents,
  waiting on insurance, orientation scheduled, started working, 14 days
  completed.
- Rejection **requires a reason** that the referrer sees.
- Referrer **payout is triggered at 14 days** of the driver working. The
  admin marks the referral paid when the payout has been issued.
