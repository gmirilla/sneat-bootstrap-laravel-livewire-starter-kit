<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family:Arial,sans-serif;color:#333;max-width:600px;margin:0 auto;padding:20px">

<h2 style="color:#1a56db;border-bottom:2px solid #1a56db;padding-bottom:8px">
    Claim Notification Received
</h2>

<p>A new claim notification has been submitted. Details below.</p>

<table style="width:100%;border-collapse:collapse;margin:16px 0">
    <tr style="background:#f3f4f6">
        <td style="padding:8px 12px;font-weight:bold;width:35%">Reference</td>
        <td style="padding:8px 12px">{{ $notification->reference_no }}</td>
    </tr>
    <tr>
        <td style="padding:8px 12px;font-weight:bold">Policy Number</td>
        <td style="padding:8px 12px">{{ $notification->policy_no }}</td>
    </tr>
    <tr style="background:#f3f4f6">
        <td style="padding:8px 12px;font-weight:bold">Policy Type</td>
        <td style="padding:8px 12px">{{ $notification->policy_type }}</td>
    </tr>
    <tr>
        <td style="padding:8px 12px;font-weight:bold">Policy Period</td>
        <td style="padding:8px 12px">
            {{ $notification->policy_start->format('d M Y') }} – {{ $notification->policy_end->format('d M Y') }}
        </td>
    </tr>
    <tr style="background:#f3f4f6">
        <td style="padding:8px 12px;font-weight:bold">Claimant Name</td>
        <td style="padding:8px 12px">{{ $notification->claimant_name }}</td>
    </tr>
    <tr>
        <td style="padding:8px 12px;font-weight:bold">Claimant Email</td>
        <td style="padding:8px 12px">{{ $notification->claimant_email }}</td>
    </tr>
    <tr style="background:#f3f4f6">
        <td style="padding:8px 12px;font-weight:bold">Claimant Phone</td>
        <td style="padding:8px 12px">{{ $notification->claimant_phone }}</td>
    </tr>
    <tr>
        <td style="padding:8px 12px;font-weight:bold">Date of Incident</td>
        <td style="padding:8px 12px">{{ $notification->incident_date->format('d M Y') }}</td>
    </tr>
</table>

<h3 style="margin-top:24px">Incident Description</h3>
<p style="background:#f9fafb;padding:12px;border-left:4px solid #1a56db;white-space:pre-wrap">{{ $notification->description }}</p>

@if ($accountCreated)
<p style="background:#fef3c7;padding:10px 14px;border-radius:4px;margin-top:16px">
    <strong>Note:</strong> A new MySalam account has been created for this claimant and is <strong>pending admin verification</strong>.
    Please review and approve or reject the account from the admin panel.
</p>
@elseif ($accountPending)
<p style="background:#fef3c7;padding:10px 14px;border-radius:4px;margin-top:16px">
    <strong>Note:</strong> This claimant has an existing account that is still <strong>pending admin verification</strong>.
</p>
@endif

<p style="color:#6b7280;font-size:13px;margin-top:32px;border-top:1px solid #e5e7eb;padding-top:12px">
    Reply directly to this email to contact the claimant. Their address is pre-filled as the reply-to.<br>
    Always include the reference number <strong>{{ $notification->reference_no }}</strong> in all correspondence.
</p>

</body>
</html>
