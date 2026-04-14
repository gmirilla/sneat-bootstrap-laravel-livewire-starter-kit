<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; font-size: 14px; }
        .header { background: #B18752; color: #fff; padding: 16px 24px; }
        .body   { padding: 24px; }
        .field  { margin-bottom: 12px; }
        .label  { font-weight: bold; color: #555; }
        .message-box { background: #f7f9f8; border-left: 4px solid #B18752; padding: 12px 16px; margin-top: 8px; white-space: pre-wrap; }
        .footer { color: #999; font-size: 12px; padding: 16px 24px; border-top: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="header">
        <strong>Salam Takaful — Claim Enquiry</strong>
    </div>
    <div class="body">
        <div class="field"><span class="label">From:</span> {{ $senderName }} &lt;{{ $senderEmail }}&gt;</div>
        @if($claimNo)
        <div class="field"><span class="label">Claim No:</span> {{ $claimNo }}</div>
        @endif
        @if($policyNo)
        <div class="field"><span class="label">Policy No:</span> {{ $policyNo }}</div>
        @endif
        <div class="field">
            <span class="label">Message:</span>
            <div class="message-box">{{ $messageBody }}</div>
        </div>
    </div>
    <div class="footer">
        This message was sent via the Salam Online claims portal. Reply directly to this email to respond to the sender.
    </div>
</body>
</html>
