# Deploying to Hostinger (PHP + MySQL)

This app runs on plain PHP 8 with a MySQL database. It works on **any
Hostinger plan that includes PHP and MySQL** — no Node.js required.

The walkthrough below assumes:
- You own `otrexpressgroup.com` in hPanel.
- You've already created a subdomain `referrals.otrexpressgroup.com`.
  Hostinger auto-created a folder for it, typically something like
  `domains/otrexpressgroup.com/public_html/referrals/`.

## Step 1 — Create the MySQL database

1. hPanel → **Databases → Management** → **New MySQL database**.
2. Fill in:
   - Database name: `u000000000_referrals` (Hostinger prefixes with your user ID)
   - Username: `u000000000_referrals`
   - Password: generate and copy it somewhere safe.
3. Click **Create**. Note:
   - **Host** (usually `localhost`)
   - **Database name**
   - **Username**
   - **Password**

You'll need these in Step 4.

## Step 2 — Upload the code

You have two options.

### Option A — Connect GitHub (recommended; deploys on every push)

1. Push this repo to GitHub (branch `claude/driver-referral-program-wkcG0`
   or whatever branch you want to deploy).
2. hPanel → **Advanced → Git** → **Create repository**:
   - **Repository address**:
     - Public repo: `https://github.com/pzabile/otrexpressreferals.git`
     - Private repo: `git@github.com:pzabile/otrexpressreferals.git` — Hostinger shows a public key, add it to GitHub as a **Deploy key** (Settings → Deploy keys → Add deploy key).
   - **Branch**: your deploy branch.
   - **Install path**: the subdomain folder, e.g. `domains/otrexpressgroup.com/public_html/referrals`.
3. Click **Create**. Hostinger clones the repo into that folder.
4. Optional — turn on auto-deploy: open the repo in hPanel → copy the
   **Webhook URL** → on GitHub add a webhook (Settings → Webhooks) with
   that URL and the "push" event.

Now every `git push` on your laptop pulls automatically on Hostinger.

### Option B — Upload via File Manager

hPanel → **File Manager** → open your subdomain folder → upload all files
from this repo into it. Don't forget the hidden files (`.htaccess`,
`.gitignore`).

## Step 3 — Create `config.php`

In File Manager, copy `config.example.php` to `config.php` (right-click →
Copy). Open `config.php` in the editor and fill in:

```php
return [
    'db_host'  => 'localhost',
    'db_port'  => 3306,
    'db_name'  => 'u000000000_referrals',
    'db_user'  => 'u000000000_referrals',
    'db_pass'  => 'your-database-password',
    'db_charset' => 'utf8mb4',

    'site_name'       => 'OTR Express Driver Referrals',
    'site_url'        => 'https://referrals.otrexpressgroup.com',
    'referral_bounty' => 500,

    'session_name'    => 'otr_ref_sess',
    'app_secret'      => 'GENERATE-A-LONG-RANDOM-STRING-32-CHARS-OR-MORE',
];
```

For `app_secret`, generate something random. You can use
[random.org](https://www.random.org/strings/?num=1&len=32&digits=on&upperalpha=on&loweralpha=on&unique=on&format=plain&rnd=new)
or any password generator — just make it 32+ characters of gibberish.

`config.php` is in `.gitignore`, so it will never leave your server —
that's on purpose.

## Step 4 — Run the install wizard

Open `https://referrals.otrexpressgroup.com/install.php` in your browser.

1. It confirms the DB credentials it will use.
2. Enter your admin email and a strong password (min 8 chars).
3. Click **Run Install**.

It creates all tables and your admin account.

**Then delete `install.php` from your server** (File Manager → right-click
→ Delete). It should never be reachable in production.

## Step 5 — Enable HTTPS

hPanel → **SSL** → install a free SSL certificate for
`referrals.otrexpressgroup.com`.

## Step 6 — Try it

- `https://referrals.otrexpressgroup.com/` — landing page
- `https://referrals.otrexpressgroup.com/refer.php` — referral form
- `https://referrals.otrexpressgroup.com/status.php` — status lookup
- `https://referrals.otrexpressgroup.com/admin/` — admin console

Log in at `/admin/login.php` with the credentials you set in the wizard.

## Updating the site later

**If you used Git (Option A):**

1. On your laptop:
   ```
   git add -A && git commit -m "…" && git push
   ```
2. On Hostinger: hPanel → Git → click **Deploy** (or the webhook does it
   automatically).
3. If `sql/schema.sql` changed, open phpMyAdmin (hPanel → Databases →
   phpMyAdmin → your DB → **Import**) and import the new schema. (The
   `CREATE TABLE IF NOT EXISTS` statements are idempotent.)

**If you used File Manager (Option B):**

Just re-upload the changed files.

No rebuild, no PHP restart — Apache picks up new files on the next
request.

## Common gotchas

- **"Configuration missing"** on page load → you haven't created `config.php`
  yet. Copy `config.example.php` to `config.php` and edit it.
- **"Database connection failed"** → username/password/db name wrong in
  `config.php`. Double-check against hPanel → Databases.
- **Install wizard says "Set app_secret"** → you left the placeholder
  string in `config.php`. Replace it with a random 32+ char string.
- **Can't reach `/admin/`** → make sure HTTPS is on; the admin session
  cookie is `Secure` under HTTPS only.
- **Status lookup always shows "No referrals found"** → the referrer
  looked up with an email or phone that doesn't match what they typed on
  the referral form. Ask them to try the other field.
- **Anything weird** → hPanel → Advanced → **Error Logs** has your PHP
  errors. That's usually enough to spot the issue.
