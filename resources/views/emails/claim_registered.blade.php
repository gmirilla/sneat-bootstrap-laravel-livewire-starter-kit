<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family:Arial,sans-serif;color:#333;max-width:600px;margin:0 auto;padding:20px">

<h2 style="color:#16a34a;border-bottom:2px solid #16a34a;padding-bottom:8px">
    Your Claim Has Been Registered — MySalam Takaful
</h2>

<p>Dear {{ $notification->claimant_name }},</p>

<p>
    Your claim notification has been reviewed and officially registered with our claims department.
    Please use your <strong>Elite Claim Number</strong> in all future correspondence with the claims team.
</p>

<table style="width:100%;border-collapse:collapse;margin:20px 0">
    <tr style="background:#f0fdf4">
        <td style="padding:10px 14px;font-weight:bold;width:40%">Elite Claim Number</td>
        <td style="padding:10px 14px;font-weight:bold;font-size:1.15em;color:#16a34a">
            {{ $notification->elite_claim_no }}
        </td>
    </tr>
    <tr>
        <td style="padding:8px 14px;font-weight:bold">MySalam Reference</td>
        <td style="padding:8px 14px;color:#6b7280">{{ $notification->reference_no }}</td>
    </tr>
    <tr style="background:#f9fafb">
        <td style="padding:8px 14px;font-weight:bold">Policy Number</td>
        <td style="padding:8px 14px">{{ $notification->policy_no }}</td>
    </tr>
    <tr>
        <td style="padding:8px 14px;font-weight:bold">Policy Type</td>
        <td style="padding:8px 14px">{{ $notification->policy_type }}</td>
    </tr>
    <tr style="background:#f9fafb">
        <td style="padding:8px 14px;font-weight:bold">Date of Incident</td>
        <td style="padding:8px 14px">{{ $notification->incident_date->format('d M Y') }}</td>
    </tr>
</table>

<p style="background:#f0fdf4;padding:12px 16px;border-radius:4px;border-left:4px solid #16a34a">
    <strong>What happens next?</strong><br>
    A claims officer will contact you at <strong>{{ $notification->claimant_email }}</strong> to guide you
    through the assessment process. Please keep your Elite Claim Number handy — you will need it when
    calling or emailing the claims team.
</p>

<p style="color:#6b7280;font-size:13px;margin-top:32px;border-top:1px solid #e5e7eb;padding-top:12px">
    MySalam Takaful Insurance &nbsp;|&nbsp; claims@salamtakafulinsurance.com<br>
    If you did not submit this claim, please contact us immediately quoting {{ $notification->reference_no }}.
</p>

</body>
</html>
