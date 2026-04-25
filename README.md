# OTR Express — Driver Referral Program

A PHP + MySQL web app where drivers / fleet contacts can refer new CDL
drivers to OTR Express, track every stage of the hiring pipeline, and earn
a payout when the referred driver completes 14 days on the road.

Built to run directly on Hostinger shared hosting — no build step, no
Node.js, no frameworks. Just upload the files, point a MySQL database at
it, and run the install wizard.

## Features

- **Public landing page** explaining the program, payout rules, and pipeline.
- **Refer a Driver** form capturing referrer + driver (name, email, phone),
  with TCPA-style consent checkboxes and a link to Terms.
- **Referrer status page** — no account needed. Enter the email or phone
  used on the referral to see every referral's timeline, admin notes, and
  rejection reasons.
- **Terms & Conditions page** at `/terms.php` covering eligibility, payout
  rules, driver consent, TCPA, privacy, and disputes.
- **Telegram notification** sent to a configured chat when a new referral
  is submitted — fire-and-forget, never blocks the user if Telegram is
  unreachable.
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

## Project layout

```
/
├── index.php                  # Landing page
├── refer.php                  # Referral form
├── refer-submit.php           # Form handler
├── thanks.php                 # Confirmation
├── status.php                 # Referrer status lookup
├── terms.php                  # Terms & Conditions
├── install.php                # One-time setup wizard (delete after run)
├── config.example.php         # Copy to config.php and edit
├── .htaccess                  # Apache hardening rules
├── admin/
│   ├── index.php              # Dashboard
│   ├── login.php
│   ├── logout.php
│   ├── referral.php           # Detail view: stage updates + comments
│   └── actions.php            # Form action handler
├── assets/style.css
├── includes/
│   ├── bootstrap.php          # Loads config, opens DB, starts session
│   ├── db.php                 # PDO connection
│   ├── auth.php               # Admin session helpers
│   ├── statuses.php           # Status constants + labels
│   ├── helpers.php            # CSRF, flash, formatting, escape
│   ├── telegram.php           # Telegram notifier
│   ├── layout-public.php
│   └── layout-admin.php
└── sql/
    └── schema.sql             # Run via phpMyAdmin, or install.php
```

## Deploying to Hostinger

See [HOSTINGER.md](./HOSTINGER.md) for step-by-step instructions, including
connecting your GitHub repo to Hostinger so updates deploy with
`git push`.

## Requirements

- PHP 8.0 or newer (Hostinger default is PHP 8.x)
- MySQL 5.7+ / MariaDB 10.3+
- mod_rewrite not strictly required (all URLs keep their `.php` extension)

## Security notes

- Admin passwords are stored as `password_hash` / `password_verify` (bcrypt).
- All admin forms use CSRF tokens bound to the session.
- All database access uses prepared statements (PDO).
- All user-supplied output is escaped via `htmlspecialchars`.
- `config.php` and `includes/` are protected by `.htaccess`.
- Session cookies are `HttpOnly`, `SameSite=Lax`, and `Secure` under HTTPS.
- `install.php` should be deleted from the server after setup.
