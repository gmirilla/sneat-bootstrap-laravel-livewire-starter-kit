<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family:Arial,sans-serif;color:#333;max-width:600px;margin:0 auto;padding:20px">

<h2 style="color:#1a56db;border-bottom:2px solid #1a56db;padding-bottom:8px">
    Your Claim Has Been Received — MySalam Takaful
</h2>

<p>Dear {{ $user->firstname ?: $user->name }},</p>

<p>Thank you for submitting your claim notification. We have received it and our claims team will be in touch.</p>

<table style="width:100%;border-collapse:collapse;margin:16px 0">
    <tr style="background:#f3f4f6">
        <td style="padding:8px 12px;font-weight:bold;width:40%">Claim Reference</td>
        <td style="padding:8px 12px;font-weight:bold;color:#1a56db">{{ $claimReference }}</td>
    </tr>
</table>

<p>Please keep your reference number for all correspondence with our claims team.</p>

<hr style="border:none;border-top:1px solid #e5e7eb;margin:24px 0">

<h3 style="color:#374151">Your MySalam Account</h3>

<p>
    We have created an online account for you using the email address you provided.
    For your security, an administrator will verify your identity before the account is activated.
    Once verified, you will receive a separate email with instructions to set your password and log in.
</p>

<p style="background:#fef3c7;padding:10px 14px;border-radius:4px">
    <strong>No action is required from you at this stage.</strong>
    Your claim notification has already been sent to our claims team regardless of account status.
</p>

<p style="color:#6b7280;font-size:13px;margin-top:32px;border-top:1px solid #e5e7eb;padding-top:12px">
    MySalam Takaful Insurance &nbsp;|&nbsp; claims@salamtakafulinsurance.com<br>
    If you did not submit a claim, please contact us immediately.
</p>

</body>
</html>
