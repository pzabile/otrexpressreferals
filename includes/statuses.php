<?php
declare(strict_types=1);

const OTR_STATUS_ORDER = [
    'SUBMITTED',
    'CONTACTED',
    'APPLICATION_SENT',
    'WAITING_ON_DOCUMENTS',
    'WAITING_ON_INSURANCE',
    'ORIENTATION_SCHEDULED',
    'STARTED_WORKING',
    'TWO_WEEKS_COMPLETED',
    'HIRED_PAID', // Underlying constant kept for DB compatibility; label is "Paid".
    'REJECTED',
];

function status_label(string $status): string
{
    $labels = [
        'SUBMITTED' => 'Referral Submitted',
        'CONTACTED' => 'Driver Contacted',
        'APPLICATION_SENT' => 'Application Sent',
        'WAITING_ON_DOCUMENTS' => 'Waiting on Documents',
        'WAITING_ON_INSURANCE' => 'Waiting on Insurance',
        'ORIENTATION_SCHEDULED' => 'Orientation Scheduled',
        'STARTED_WORKING' => 'Started Working',
        'TWO_WEEKS_COMPLETED' => '14 Days Completed — Payout Eligible',
        'HIRED_PAID' => 'Paid',
        'REJECTED' => 'Not Selected',
    ];
    return $labels[$status] ?? $status;
}

function status_description(string $status): string
{
    $d = [
        'SUBMITTED' => 'We received your referral and it\'s in the queue.',
        'CONTACTED' => 'Our recruiter has reached out to the driver.',
        'APPLICATION_SENT' => 'Driver was sent the application packet.',
        'WAITING_ON_DOCUMENTS' => 'Awaiting documents required for hiring.',
        'WAITING_ON_INSURANCE' => 'Under insurance review.',
        'ORIENTATION_SCHEDULED' => 'Driver is scheduled to attend orientation.',
        'STARTED_WORKING' => 'Driver started working. 14-day clock is running.',
        'TWO_WEEKS_COMPLETED' => 'Driver completed 14 days. We\'ll contact you on the phone or email you provided to arrange payment details.',
        'HIRED_PAID' => 'Referrer payout has been issued. Thanks for the referral!',
        'REJECTED' => 'This referral was not accepted. See the reason on your status page.',
    ];
    return $d[$status] ?? '';
}

function status_css_class(string $status): string
{
    $map = [
        'SUBMITTED' => 'status-sky',
        'CONTACTED' => 'status-indigo',
        'APPLICATION_SENT' => 'status-violet',
        'WAITING_ON_DOCUMENTS' => 'status-amber',
        'WAITING_ON_INSURANCE' => 'status-amber',
        'ORIENTATION_SCHEDULED' => 'status-cyan',
        'STARTED_WORKING' => 'status-emerald',
        'TWO_WEEKS_COMPLETED' => 'status-emerald-strong',
        'HIRED_PAID' => 'status-brand',
        'REJECTED' => 'status-rose',
    ];
    return $map[$status] ?? 'status-slate';
}

function is_valid_status(string $status): bool
{
    return in_array($status, OTR_STATUS_ORDER, true);
}

/**
 * Render a status chip.
 */
function status_badge(string $status): string
{
    return '<span class="chip ' . e(status_css_class($status)) . '">'
        . '<span class="dot"></span>'
        . e(status_label($status))
        . '</span>';
}
