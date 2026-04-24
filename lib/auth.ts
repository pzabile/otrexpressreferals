import { cookies } from "next/headers";
import crypto from "node:crypto";
import bcrypt from "bcryptjs";
import { prisma } from "./db";

const COOKIE_NAME = "otr_admin_session";
const COOKIE_MAX_AGE = 60 * 60 * 12; // 12 hours

function getSecret(): string {
  const secret = process.env.SESSION_SECRET;
  if (!secret || secret.length < 16) {
    throw new Error(
      "SESSION_SECRET env var must be set to a 16+ character random string."
    );
  }
  return secret;
}

function sign(payload: string): string {
  return crypto
    .createHmac("sha256", getSecret())
    .update(payload)
    .digest("hex");
}

function timingSafeEqual(a: string, b: string): boolean {
  const ab = Buffer.from(a);
  const bb = Buffer.from(b);
  if (ab.length !== bb.length) return false;
  return crypto.timingSafeEqual(ab, bb);
}

function buildToken(adminId: string): string {
  const payload = `${adminId}.${Date.now()}`;
  const signature = sign(payload);
  return `${payload}.${signature}`;
}

function parseToken(token: string): { adminId: string; issuedAt: number } | null {
  const parts = token.split(".");
  if (parts.length !== 3) return null;
  const [adminId, issuedAt, signature] = parts;
  const expected = sign(`${adminId}.${issuedAt}`);
  if (!timingSafeEqual(signature, expected)) return null;
  const issuedAtMs = Number(issuedAt);
  if (!Number.isFinite(issuedAtMs)) return null;
  if (Date.now() - issuedAtMs > COOKIE_MAX_AGE * 1000) return null;
  return { adminId, issuedAt: issuedAtMs };
}

export async function ensureAdminSeed(): Promise<void> {
  const email = process.env.ADMIN_EMAIL?.trim().toLowerCase();
  const password = process.env.ADMIN_PASSWORD;
  if (!email || !password) return;
  const existing = await prisma.adminUser.findUnique({ where: { email } });
  if (existing) return;
  const passwordHash = await bcrypt.hash(password, 10);
  await prisma.adminUser.create({ data: { email, passwordHash } });
}

export async function verifyAdminCredentials(
  email: string,
  password: string
): Promise<{ id: string; email: string } | null> {
  await ensureAdminSeed();
  const normalized = email.trim().toLowerCase();
  const admin = await prisma.adminUser.findUnique({
    where: { email: normalized }
  });
  if (!admin) return null;
  const ok = await bcrypt.compare(password, admin.passwordHash);
  if (!ok) return null;
  return { id: admin.id, email: admin.email };
}

export async function startAdminSession(adminId: string): Promise<void> {
  const token = buildToken(adminId);
  cookies().set(COOKIE_NAME, token, {
    httpOnly: true,
    sameSite: "lax",
    secure: process.env.NODE_ENV === "production",
    path: "/",
    maxAge: COOKIE_MAX_AGE
  });
}

export async function endAdminSession(): Promise<void> {
  cookies().set(COOKIE_NAME, "", { path: "/", maxAge: 0 });
}

export async function getCurrentAdmin(): Promise<{ id: string; email: string } | null> {
  const token = cookies().get(COOKIE_NAME)?.value;
  if (!token) return null;
  const parsed = parseToken(token);
  if (!parsed) return null;
  const admin = await prisma.adminUser.findUnique({
    where: { id: parsed.adminId }
  });
  if (!admin) return null;
  return { id: admin.id, email: admin.email };
}

export async function requireAdmin(): Promise<{ id: string; email: string }> {
  const admin = await getCurrentAdmin();
  if (!admin) {
    throw new Error("UNAUTHORIZED");
  }
  return admin;
}
