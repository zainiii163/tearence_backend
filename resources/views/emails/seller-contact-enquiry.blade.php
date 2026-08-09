<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Buyer enquiry</title>
</head>
<body style="font-family: Arial, sans-serif; color: #0f172a; line-height: 1.5; max-width: 640px; margin: 0 auto; padding: 24px;">
    <p>Hello{{ $sellerName ? ' ' . e($sellerName) : '' }},</p>
    <p>You have a new buyer enquiry on your listing:</p>
    <p style="font-size: 18px; font-weight: bold; margin: 8px 0;">{{ $listingTitle }}</p>

    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin: 16px 0;">
        <p style="margin: 0 0 8px;"><strong>From:</strong> {{ $buyerName }} &lt;{{ $buyerEmail }}&gt;</p>
        @if ($buyerPhone)
            <p style="margin: 0 0 8px;"><strong>Phone:</strong> {{ $buyerPhone }}</p>
        @endif
        <p style="margin: 0 0 8px;"><strong>Preferred contact:</strong> {{ ucfirst($contactMethod) }}</p>
        <p style="margin: 12px 0 0; white-space: pre-wrap;">{{ $enquiryMessage }}</p>
    </div>

    @if ($listingUrl)
        <p><a href="{{ $listingUrl }}" style="color: #0f766e;">View your listing</a></p>
    @endif

    <p style="color: #64748b; font-size: 13px;">You can reply directly to this email to contact the buyer.</p>
    <p>— Worldwide Adverts</p>
</body>
</html>
