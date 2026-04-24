# Deploying to Hostinger

This app is a Next.js 14 server-rendered application with a Prisma database.
It needs a **Node.js runtime** to run. That means:

| Hostinger plan | Supported? |
| --- | --- |
| Shared (PHP-only, "Single/Premium Web Hosting" without Node.js) | No |
| Premium / Business / Cloud Hosting with **Node.js** selector | Yes |
| **VPS** (KVM 1/2/4/…) with Ubuntu/Debian | Yes — recommended |

If you're on a plan without Node.js, upgrade or switch to a VPS before
continuing.

---

## Option A — Hostinger VPS (recommended)

Give yourself the most reliable environment. Works with SQLite or a managed
database.

### 1. Prepare the VPS

```bash
# SSH into the VPS
ssh root@your-vps-ip

# Install Node 20 LTS
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt-get install -y nodejs git nginx

# Install pm2 globally to keep the app alive
npm install -g pm2
```

### 2. Get the code

```bash
cd /var/www
git clone <your-repo-url> otr-referrals
cd otr-referrals
```

### 3. Configure environment

```bash
cp .env.example .env
nano .env
```

Set at minimum:

```
DATABASE_URL="file:./prisma/prod.db"
ADMIN_EMAIL="you@yourdomain.com"
ADMIN_PASSWORD="a-strong-password"
SESSION_SECRET="$(openssl rand -hex 32)"
NEXT_PUBLIC_SITE_URL="https://yourdomain.com"
NEXT_PUBLIC_REFERRAL_BOUNTY="500"
```

Replace `SESSION_SECRET` with a long random string (the `openssl` command
above prints one).

### 4. Install, build, create the database

```bash
npm install
npx prisma db push          # creates SQLite file and tables
npm run build
```

### 5. Start with PM2

```bash
pm2 start npm --name otr-referrals -- run start
pm2 save
pm2 startup                 # follow the printed instruction
```

The app is now listening on `http://127.0.0.1:3000`.

### 6. Put Nginx in front with HTTPS

Create `/etc/nginx/sites-available/otr-referrals`:

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;

    location / {
        proxy_pass http://127.0.0.1:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

Enable and reload:

```bash
ln -s /etc/nginx/sites-available/otr-referrals /etc/nginx/sites-enabled/
nginx -t && systemctl reload nginx
```

Add HTTPS with Let's Encrypt:

```bash
apt-get install -y certbot python3-certbot-nginx
certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

Done. Visit `https://yourdomain.com`.

### Updating

```bash
cd /var/www/otr-referrals
git pull
npm install
npx prisma db push
npm run build
pm2 restart otr-referrals
```

---

## Option B — Hostinger Node.js shared hosting (Premium / Business / Cloud)

Hostinger's shared plans with the **Node.js** selector run your app under
Phusion Passenger. This project ships a `server.js` made for Passenger.

### 1. Create a Node.js app in hPanel

1. hPanel → **Advanced → Node.js** (or **Website → Node.js**).
2. Click **Create application**.
3. Node.js version: **20.x** (or latest LTS offered).
4. Application mode: **Production**.
5. Application root: e.g. `public_html/referrals` (create this folder in File
   Manager if needed).
6. Application URL: pick your domain/subdomain.
7. **Application startup file**: `server.js`
8. Save.

### 2. Use a database Hostinger supports

SQLite will technically work but the database file lives in your hosting
account's file system. For reliability, switch to **MySQL** (Hostinger gives
you MySQL in hPanel).

1. hPanel → **Databases → Management** → create a new database + user.
2. Note the host, database name, username, password.
3. In this project, edit `prisma/schema.prisma`:

   ```prisma
   datasource db {
     provider = "mysql"
     url      = env("DATABASE_URL")
   }
   ```

4. In your `.env` set:

   ```
   DATABASE_URL="mysql://USER:PASSWORD@HOST:3306/DBNAME"
   ```

(If you prefer to stay on SQLite, skip step 3 and set
`DATABASE_URL="file:./prisma/prod.db"`.)

### 3. Upload the code

Either connect git in hPanel or upload the folder via File Manager / SFTP.
Do **not** upload `node_modules` or `.next` — Hostinger will build them.

### 4. Set environment variables

Back in hPanel → Node.js app → **Environment variables**, add:

```
DATABASE_URL=...
ADMIN_EMAIL=you@yourdomain.com
ADMIN_PASSWORD=a-strong-password
SESSION_SECRET=a-long-random-string-32-chars-or-more
NEXT_PUBLIC_SITE_URL=https://yourdomain.com
NEXT_PUBLIC_REFERRAL_BOUNTY=500
NODE_ENV=production
```

### 5. Install dependencies, create tables, build

From the Node.js app page, open the **Terminal** (or SSH in and `cd` to the
app root), then:

```bash
npm install
npx prisma db push
npm run build
```

### 6. Start / restart

Use the **Restart** button in the Node.js app page. Passenger will run
`server.js`. Visit your domain.

---

## Files that matter for Hostinger

- `server.js` — Passenger entry point (Option B).
- `package.json`
  - `engines.node` pins the Node version so hPanel provisions a compatible runtime.
  - `prisma` CLI is in `dependencies` so it survives `npm install --production`.
  - `start` runs `next start` (VPS / `npm start`); `start:passenger` runs `server.js`.
- `.env` — never commit this; recreate it on the server or in the Node.js env vars panel.
- `prisma/schema.prisma` — change `provider` to `mysql` if you use Hostinger MySQL.

## Common gotchas

- **"Prisma Client not generated"** — run `npm install` again or
  `npx prisma generate`.
- **"Environment variable not found: DATABASE_URL"** — the app was started
  without the env var set. Recheck hPanel env vars and restart.
- **"Tables do not exist"** — you forgot `npx prisma db push` after deploy.
- **Port issues on VPS** — make sure `next start` is bound to 127.0.0.1:3000
  (the default) and Nginx proxies to it. Never expose port 3000 to the
  internet directly.
- **SQLite on shared hosting** — fine for low volume, but any file-system
  reset on the hosting side wipes data. Prefer MySQL on shared hosting.
