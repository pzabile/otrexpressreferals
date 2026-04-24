// Passenger-compatible entry point for Hostinger Node.js hosting.
// On Hostinger Premium/Business Node.js plans, set this file as the
// application "Startup file". On a VPS you don't need this — use
// `npm run start` instead (which runs `next start`).

const { createServer } = require("http");
const { parse } = require("url");
const next = require("next");

const port = Number(process.env.PORT) || 3000;
const hostname = process.env.HOSTNAME || "0.0.0.0";
const dev = process.env.NODE_ENV !== "production";

const app = next({ dev, hostname, port });
const handle = app.getRequestHandler();

app
  .prepare()
  .then(() => {
    createServer((req, res) => {
      const parsedUrl = parse(req.url || "/", true);
      handle(req, res, parsedUrl);
    }).listen(port, () => {
      console.log(`> Ready on http://${hostname}:${port}`);
    });
  })
  .catch((err) => {
    console.error("Fatal error starting Next.js server:", err);
    process.exit(1);
  });
