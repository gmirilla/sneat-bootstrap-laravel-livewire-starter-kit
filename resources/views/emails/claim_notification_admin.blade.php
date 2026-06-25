<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family:Arial,sans-serif;color:#333;max-width:600px;margin:0 auto;padding:20px">

<h2 style="color:#d97706;border-bottom:2px solid #d97706;padding-bottom:8px">
    Action Required — New Pending Account
</h2>

<p>A new customer account was automatically created when a claim notification was submitted. Please review and verify this account.</p>

<table style="width:100%;border-collapse:collapse;margin:16px 0">
    <tr style="background:#f3f4f6">
        <td style="padding:8px 12px;font-weight:bold;width:35%">Claim Reference</td>
        <td style="padding:8px 12px;color:#1a56db;font-weight:bold">{{ $notification->reference_no }}</td>
    </tr>
    <tr>
        <td style="padding:8px 12px;font-weight:bold">Policy Number</td>
        <td style="padding:8px 12px">{{ $notification->policy_no }}</td>
    </tr>
    <tr style="background:#f3f4f6">
        <td style="padding:8px 12px;font-weight:bold">Account Name</td>
        <td style="padding:8px 12px">{{ $pendingUser->name }}</td>
    </tr>
    <tr>
        <td style="padding:8px 12px;font-weight:bold">Account Email</td>
        <td style="padding:8px 12px">{{ $pendingUser->email }}</td>
    </tr>
    <tr style="background:#f3f4f6">
        <td style="padding:8px 12px;font-weight:bold">Phone</td>
        <td style="padding:8px 12px">{{ $pendingUser->telno }}</td>
    </tr>
    <tr>
        <td style="padding:8px 12px;font-weight:bold">Registered</td>
        <td style="padding:8px 12px">{{ $pendingUser->created_at->format('d M Y H:i') }}</td>
    </tr>
</table>

<p>
    To approve or reject this account, log in to the admin panel and visit
    <strong>User Management → Pending Accounts</strong>.
</p>

<p style="color:#6b7280;font-size:13px;margin-top:32px;border-top:1px solid #e5e7eb;padding-top:12px">
    The claimant has been notified that their account is pending review.
    The full claim notification has been sent in a separate email.
</p>

</body>
</html>
