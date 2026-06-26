<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family:Arial,sans-serif;color:#333;max-width:600px;margin:0 auto;padding:20px">

<h2 style="color:#dc2626;border-bottom:2px solid #dc2626;padding-bottom:8px">
    Account Verification Unsuccessful — MySalam Takaful
</h2>

<p>Dear {{ $user->firstname ?: $user->name }},</p>

<p>
    Thank you for submitting your claim notification. After review, we were unable to
    verify your identity and your MySalam account could not be activated at this time.
</p>

<p>
    <strong>Your claim notification has already been received by our claims team</strong>
    and is being processed regardless of account status. You do not need to re-submit it.
</p>

<p>
    Please contact our claims team directly and quote your reference number to follow up
    on your claim:
</p>

<p style="background:#fef2f2;padding:10px 14px;border-radius:4px;border-left:4px solid #dc2626">
    If you believe this decision is incorrect, please contact us at
    <a href="mailto:claims@salamtakafulinsurance.com">claims@salamtakafulinsurance.com</a>
    with a copy of a government-issued ID and your policy number.
</p>

<p style="color:#6b7280;font-size:13px;margin-top:32px;border-top:1px solid #e5e7eb;padding-top:12px">
    MySalam Takaful Insurance &nbsp;|&nbsp; claims@salamtakafulinsurance.com<br>
    If you did not submit a claim, please contact us immediately.
</p>

</body>
</html>
