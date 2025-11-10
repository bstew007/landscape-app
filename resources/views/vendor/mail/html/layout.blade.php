<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>{{ $title ?? 'Estimate Notification' }}</title>
    <style>
        body { font-family: "Segoe UI", system-ui, -apple-system, BlinkMacSystemFont, "Helvetica Neue", sans-serif; background: #f4f5f7; margin: 0; padding: 0; }
        .content { width: 100%; max-width: 600px; margin: 40px auto; background: white; border-radius: 8px; padding: 32px; box-shadow: 0 20px 45px rgba(15,23,42,.08); }
        .footer { font-size: 12px; color: #9ca3af; margin-top: 24px; border-top: 1px solid #e5e7eb; padding-top: 12px; }
    </style>
</head>
<body>
<div class="content">
    {{ $slot }}
    @if (isset($footer))
        <div class="footer">{{ $footer }}</div>
    @endif
</div>
</body>
</html>
