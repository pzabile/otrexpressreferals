"use server";

import { z } from "zod";
import { prisma } from "@/lib/db";
import { redirect } from "next/navigation";

const ReferralSchema = z.object({
  referrerName: z.string().trim().min(2, "Your name is required."),
  referrerEmail: z
    .string()
    .trim()
    .toLowerCase()
    .email("Enter a valid email address."),
  referrerPhone: z
    .string()
    .trim()
    .min(7, "Enter a valid phone number.")
    .max(30),
  driverName: z.string().trim().min(2, "Driver name is required."),
  driverEmail: z
    .string()
    .trim()
    .toLowerCase()
    .email("Driver email is required."),
  driverPhone: z
    .string()
    .trim()
    .min(7, "Driver phone is required.")
    .max(30),
  notes: z.string().trim().max(1000).optional()
});

export type ReferralFormState = {
  ok?: boolean;
  error?: string;
  fieldErrors?: Record<string, string>;
};

export async function submitReferral(
  _prev: ReferralFormState,
  formData: FormData
): Promise<ReferralFormState> {
  const parsed = ReferralSchema.safeParse({
    referrerName: formData.get("referrerName"),
    referrerEmail: formData.get("referrerEmail"),
    referrerPhone: formData.get("referrerPhone"),
    driverName: formData.get("driverName"),
    driverEmail: formData.get("driverEmail"),
    driverPhone: formData.get("driverPhone"),
    notes: formData.get("notes") ?? undefined
  });

  if (!parsed.success) {
    const fieldErrors: Record<string, string> = {};
    for (const issue of parsed.error.issues) {
      const key = issue.path.join(".");
      if (!fieldErrors[key]) fieldErrors[key] = issue.message;
    }
    return { ok: false, fieldErrors, error: "Please fix the highlighted fields." };
  }

  const data = parsed.data;

  const referrer = await prisma.referrer.upsert({
    where: { email: data.referrerEmail },
    update: {
      name: data.referrerName,
      phone: data.referrerPhone
    },
    create: {
      name: data.referrerName,
      email: data.referrerEmail,
      phone: data.referrerPhone
    }
  });

  const referral = await prisma.referral.create({
    data: {
      driverName: data.driverName,
      driverEmail: data.driverEmail,
      driverPhone: data.driverPhone,
      referrerId: referrer.id,
      status: "SUBMITTED",
      stageUpdates: {
        create: {
          status: "SUBMITTED",
          note: "Referral received from referrer."
        }
      },
      comments: data.notes
        ? {
            create: {
              author: "SYSTEM",
              body: `Note from referrer at submission: ${data.notes}`,
              visibleToReferrer: true
            }
          }
        : undefined
    }
  });

  redirect(`/refer/thanks?id=${referral.id}`);
}
