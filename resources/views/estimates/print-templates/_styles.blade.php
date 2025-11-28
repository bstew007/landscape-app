{{-- Shared styles for all estimate print templates --}}
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body {
        font-family: 'Helvetica', Arial, sans-serif;
        color: #1f2937;
        line-height: 1.6;
        padding: 40px;
        margin: 0;
    }
    
    .page {
        max-width: 100%;
    }
    
    /* Header with logo and company info - Using table for DomPDF compatibility */
    .header {
        width: 100%;
        padding-bottom: 20px;
        margin-bottom: 30px;
        border-bottom: 4px solid #10b981;
    }
    
    .company-name {
        font-size: 24px;
        font-weight: 700;
        color: #047857;
        margin-bottom: 8px;
    }
    
    .company-details {
        font-size: 12px;
        color: #6b7280;
        line-height: 1.8;
    }
    
    .company-details p {
        margin: 2px 0;
    }
    
    .company-logo {
        max-width: 150px;
        max-height: 70px;
        height: auto;
    }
    
    /* Document title area */
    .document-title {
        background: #047857;
        color: white;
        padding: 20px 30px;
        margin: 20px 0;
    }
    
    .document-title h1 {
        font-size: 28px;
        font-weight: 700;
        margin: 0 0 8px 0;
    }
    
    .document-title .subtitle {
        font-size: 14px;
        opacity: 0.95;
        font-weight: 500;
        margin: 0;
    }
    
    .template-badge {
        display: inline-block;
        padding: 4px 12px;
        background: rgba(255, 255, 255, 0.25);
        color: white;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 8px;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }
    
    .document-title h1 {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 8px;
        letter-spacing: -0.5px;
    }
    
    .document-title .subtitle {
        font-size: 16px;
        opacity: 0.95;
        font-weight: 500;
    }
    
    .template-badge {
        display: inline-block;
        padding: 6px 14px;
        background: rgba(255, 255, 255, 0.25);
        color: white;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 10px;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }
    
    /* Client and estimate info */
    .info-section {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-left: 4px solid #10b981;
        padding: 15px;
        margin-bottom: 25px;
    }
    
    .info-label {
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #047857;
        font-weight: 700;
        margin-bottom: 4px;
        margin-top: 8px;
    }
    
    .info-value {
        font-size: 13px;
        color: #111827;
        margin-bottom: 8px;
    }
    
    /* Section headers */
    h2 {
        font-size: 18px;
        font-weight: 700;
        color: #047857;
        margin: 25px 0 12px 0;
        padding-bottom: 6px;
        border-bottom: 2px solid #d1fae5;
    }
    
    h3 {
        font-size: 14px;
        font-weight: 600;
        color: #059669;
        margin: 15px 0 10px 0;
    }
    
    /* Tables */
    table {
        width: 100%;
        border-collapse: collapse;
        margin: 12px 0;
        font-size: 12px;
    }
    
    thead {
        background: #047857;
        color: white;
    }
    
    th {
        padding: 10px 8px;
        text-align: left;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 700;
        background: #047857;
        color: white;
    }
    
    th.text-right {
        text-align: right;
    }
    
    th.text-center {
        text-align: center;
    }
    
    tbody tr {
        border-bottom: 1px solid #e5e7eb;
    }
    }
    
    tbody tr:nth-child(even) {
        background: #f9fafb;
    }
    
    tbody tr:hover {
        background: #d1fae5;
    }
    
    td {
        padding: 10px;
        color: #374151;
    }
    
    td.text-right {
        text-align: right;
    }
    
    td.text-center {
        text-align: center;
    }
    
    td.font-medium {
        font-weight: 600;
        color: #111827;
    }
    
    /* Area/section headers in tables */
    .area-header {
        background: #d1fae5 !important;
        font-weight: 700;
        color: #047857;
        font-size: 14px;
    }
    
    .area-total {
        background: #f3f4f6 !important;
        font-weight: 600;
        color: #111827;
    }
    
    /* Totals section */
    .totals-section {
        margin-top: 30px;
        float: right;
        width: 350px;
    }
    
    .totals-row {
        overflow: hidden;
        padding: 10px 0;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .totals-label {
        float: left;
        font-size: 14px;
        color: #6b7280;
        font-weight: 500;
    }
    
    .totals-value {
        float: right;
        font-size: 14px;
        color: #111827;
        font-weight: 600;
    }
    
    .grand-total {
        background: linear-gradient(135deg, #047857 0%, #10b981 100%);
        color: white;
        padding: 16px 20px;
        margin-top: 8px;
        border-radius: 6px;
        overflow: hidden;
    }
    
    .grand-total .totals-label {
        font-size: 18px;
        font-weight: 700;
        color: white;
    }
    
    .grand-total .totals-value {
        font-size: 24px;
        font-weight: 700;
        color: white;
    }
    
    /* Notes and terms */
    .notes-section {
        background: #f9fafb;
        border-left: 4px solid #10b981;
        padding: 20px;
        margin: 30px 0;
        border-radius: 4px;
    }
    
    .notes-section h3 {
        color: #047857;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 0 0 12px 0;
        border: none;
    }
    
    .notes-section p {
        color: #374151;
        font-size: 13px;
        line-height: 1.7;
        white-space: pre-wrap;
    }
    
    /* Acceptance section */
    .acceptance-section {
        margin-top: 50px;
        padding-top: 30px;
        border-top: 2px solid #e5e7eb;
    }
    
    .acceptance-checkbox {
        padding: 16px;
        background: #f9fafb;
        border: 2px solid #10b981;
        border-radius: 6px;
        margin-bottom: 20px;
    }
    
    .signature-line {
        border-bottom: 2px solid #d1d5db;
        display: inline-block;
        width: 300px;
        margin-left: 10px;
    }
    
    /* Footer */
    .footer {
        margin-top: 50px;
        padding-top: 20px;
        border-top: 1px solid #e5e7eb;
        text-align: center;
        font-size: 11px;
        color: #9ca3af;
    }
    
    /* Page breaks */
    .page-break {
        page-break-before: always;
        margin-top: 40px;
    }
    
    /* Print optimization */
    @media print {
        body {
            padding: 0;
        }
        
        .page {
            padding: 20px;
        }
        
        .no-print {
            display: none;
        }
        
        table {
            page-break-inside: avoid;
        }
        
        .area-header {
            page-break-after: avoid;
        }
    }
    
    /* Utility classes */
    .clearfix::after {
        content: "";
        display: table;
        clear: both;
    }
    
    .text-right {
        text-align: right;
    }
    
    .text-center {
        text-align: center;
    }
    
    .font-medium {
        font-weight: 600;
    }
    
    .font-bold {
        font-weight: 700;
    }
    
    .mb-4 {
        margin-bottom: 16px;
    }
    
    .mt-4 {
        margin-top: 16px;
    }
</style>
