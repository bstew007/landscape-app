<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            background: #ffffff;
            padding: 30px 20px;
            border: 1px solid #e5e7eb;
            border-top: none;
        }
        .message {
            background: #f9fafb;
            border-left: 4px solid #3b82f6;
            padding: 15px 20px;
            margin: 20px 0;
            white-space: pre-wrap;
        }
        .details {
            background: #f3f4f6;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .details table {
            width: 100%;
            border-collapse: collapse;
        }
        .details td {
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .details td:first-child {
            color: #6b7280;
            width: 40%;
        }
        .details td:last-child {
            font-weight: 600;
            text-align: right;
        }
        .details tr:last-child td {
            border-bottom: none;
        }
        .attachment-notice {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        .button {
            display: inline-block;
            background: #3b82f6;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('app.name') }}</h1>
        <p style="margin: 5px 0 0 0; opacity: 0.9;">Estimate #{{ $estimate->id }}</p>
    </div>
    
    <div class="content">
        <p>Hello {{ $estimate->client->name }},</p>
        
        @if($customMessage)
        <div class="message">{{ $customMessage }}</div>
        @else
        <p>Thank you for your interest in our services. Please find attached your {{ $template }} estimate for review.</p>
        @endif
        
        <div class="details">
            <table>
                <tr>
                    <td>Estimate #</td>
                    <td>{{ $estimate->id }}</td>
                </tr>
                <tr>
                    <td>Client</td>
                    <td>{{ $estimate->client->name }}</td>
                </tr>
                @if($estimate->property)
                <tr>
                    <td>Property</td>
                    <td>{{ $estimate->property->name }}</td>
                </tr>
                @endif
                <tr>
                    <td>Total</td>
                    <td>${{ number_format($estimate->grand_total ?? 0, 2) }}</td>
                </tr>
            </table>
        </div>
        
        <div class="attachment-notice">
            <strong>📎 Attachment Included</strong><br>
            Your detailed estimate is attached as a PDF document to this email.
        </div>
        
        <p>If you have any questions or would like to discuss this estimate further, please don't hesitate to contact us.</p>
        
        <p>We look forward to working with you!</p>
        
        <p>
            Best regards,<br>
            <strong>{{ config('app.name') }}</strong>
        </p>
    </div>
    
    <div class="footer">
        <p>This is an automated email. Please do not reply directly to this message.</p>
    </div>
</body>
</html>
