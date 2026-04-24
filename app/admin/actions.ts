"use server";

import { redirect } from "next/navigation";
import { revalidatePath } from "next/cache";
import { z } from "zod";
import {
  endAdminSession,
  requireAdmin,
  startAdminSession,
  verifyAdminCredentials
} from "@/lib/auth";
import { prisma } from "@/lib/db";
import { STATUS_ORDER } from "@/lib/statuses";

export type LoginState = { error?: string };

export async function loginAction(
  _prev: LoginState,
  formData: FormData
): Promise<LoginState> {
  const email = String(formData.get("email") ?? "").trim();
  const password = String(formData.get("password") ?? "");
  if (!email || !password) {
    return { error: "Enter your email and password." };
  }
  const admin = await verifyAdminCredentials(email, password);
  if (!admin) {
    return { error: "Invalid credentials." };
  }
  await startAdminSession(admin.id);
  redirect("/admin");
}

export async function logoutAction() {
  await endAdminSession();
  redirect("/admin/login");
}

const StatusSchema = z.enum([...STATUS_ORDER] as [string, ...string[]]);

export async function updateStatusAction(formData: FormData) {
  await requireAdmin();
  const id = String(formData.get("referralId") ?? "");
  const statusRaw = String(formData.get("status") ?? "");
  const note = String(formData.get("note") ?? "").trim() || null;
  const rejectionReason =
    String(formData.get("rejectionReason") ?? "").trim() || null;
  if (!id) return;
  const status = StatusSchema.parse(statusRaw);
  if (status === "REJECTED" && !rejectionReason) {
    throw new Error(
      "A rejection reason is required when marking a referral as REJECTED."
    );
  }

  const existing = await prisma.referral.findUnique({ where: { id } });
  if (!existing) return;

  const now = new Date();
  const updates: Record<string, unknown> = { status };

  if (status === "REJECTED") {
    updates.rejectionReason = rejectionReason;
  } else {
    updates.rejectionReason = null;
  }

  if (status === "STARTED_WORKING" && !existing.startedWorkingAt) {
    updates.startedWorkingAt = now;
    const eligible = new Date(now);
    eligible.setDate(eligible.getDate() + 14);
    updates.payoutEligibleAt = eligible;
  }

  if (status === "TWO_WEEKS_COMPLETED" && !existing.payoutEligibleAt) {
    updates.payoutEligibleAt = now;
  }

  if (status === "HIRED_PAID" && !existing.paidAt) {
    updates.paidAt = now;
  }

  await prisma.referral.update({
    where: { id },
    data: {
      ...updates,
      stageUpdates: {
        create: {
          status,
          note:
            status === "REJECTED" && rejectionReason
              ? `Rejected: ${rejectionReason}`
              : note
        }
      }
    }
  });

  revalidatePath(`/admin/referrals/${id}`);
  revalidatePath("/admin");
}

export async function addCommentAction(formData: FormData) {
  await requireAdmin();
  const id = String(formData.get("referralId") ?? "");
  const body = String(formData.get("body") ?? "").trim();
  const visibleToReferrer =
    String(formData.get("visibleToReferrer") ?? "") === "on";
  if (!id || !body) return;
  await prisma.comment.create({
    data: {
      referralId: id,
      body,
      author: "ADMIN",
      visibleToReferrer
    }
  });
  revalidatePath(`/admin/referrals/${id}`);
}

export async function markPaidAction(formData: FormData) {
  await requireAdmin();
  const id = String(formData.get("referralId") ?? "");
  if (!id) return;
  await prisma.referral.update({
    where: { id },
    data: {
      status: "HIRED_PAID",
      paidAt: new Date(),
      stageUpdates: {
        create: {
          status: "HIRED_PAID",
          note: "Referrer payout recorded."
        }
      }
    }
  });
  revalidatePath(`/admin/referrals/${id}`);
  revalidatePath("/admin");
}
