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
        line-height: 1.5;
        padding: 20px;
        margin: 0;
        font-size: 13px;
    }
    
    .page {
        max-width: 100%;
    }
    
    /* NEW COMPACT HEADER STYLES */
    .header-new {
        display: table;
        width: 100%;
        margin-bottom: 14px;
        padding-bottom: 10px;
        border-bottom: 3px solid #3b82f6;
    }
    
    .header-left-new {
        display: table-cell;
        width: 60%;
        vertical-align: top;
    }
    
    .header-right-new {
        display: table-cell;
        width: 40%;
        text-align: right;
        vertical-align: top;
    }
    
    .estimate-title {
        font-size: 24px;
        font-weight: 700;
        color: #1f2937;
        margin: 0 0 4px 0;
    }
    
    .estimate-subtitle {
        font-size: 15px;
        color: #6b7280;
        margin: 0;
    }
    
    .company-logo-new {
        max-width: 140px;
        max-height: 80px;
        height: auto;
    }
    
    /* TWO COLUMN INFO LAYOUT */
    .two-col-info {
        display: table;
        width: 100%;
        margin-bottom: 14px;
        table-layout: fixed;
    }
    
    .info-half {
        display: table-cell;
        width: 49%;
        padding-right: 1%;
        vertical-align: top;
    }
    
    .info-half:last-child {
        padding-right: 0;
        padding-left: 1%;
    }
    
    /* BORDERED INFO TABLES */
    .info-table-bordered {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
        margin: 0;
    }
    
    .info-table-bordered td {
        padding: 5px 8px;
        border: 1px solid #d1d5db;
        text-align: left;
    }
    
    .info-table-bordered .label-cell {
        font-weight: 600;
        width: 35%;
        background: #f9fafb;
        color: #374151;
    }
    
    .info-table-bordered .table-header-dark {
        background: #3b82f6;
        color: white;
        font-weight: 700;
        text-align: center;
        padding: 6px 8px;
    }
    
    /* OLD HEADER STYLES - Keep for backward compatibility */
    .header {
        width: 100%;
        padding-bottom: 20px;
        margin-bottom: 30px;
        border-bottom: 4px solid #3b82f6;
    }
    
    .company-name {
        font-size: 24px;
        font-weight: 700;
        color: #2563eb;
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
        background: #3b82f6;
        color: white;
        padding: 15px 20px;
        margin: 15px 0;
    }
    
    .document-title h1 {
        font-size: 22px;
        font-weight: 700;
        margin: 0 0 6px 0;
        letter-spacing: -0.5px;
    }
    
    .document-title .subtitle {
        font-size: 14px;
        opacity: 0.95;
        font-weight: 500;
        margin: 0;
    }
    
    .template-badge {
        display: inline-block;
        padding: 4px 10px;
        background: rgba(255, 255, 255, 0.25);
        color: white;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 6px;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }
    
    /* Client and estimate info */
    .info-section {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-left: 4px solid #3b82f6;
        padding: 12px;
        margin-bottom: 14px;
    }
    
    .info-label {
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #2563eb;
        font-weight: 700;
        margin-bottom: 3px;
        margin-top: 6px;
    }
    
    .info-value {
        font-size: 12px;
        color: #111827;
        margin-bottom: 6px;
    }
    
    /* Section headers */
    h2 {
        font-size: 16px;
        font-weight: 700;
        color: #2563eb;
        margin: 16px 0 8px 0;
        padding-bottom: 4px;
        border-bottom: 2px solid #1f2937;
    }
    
    h3 {
        font-size: 13px;
        font-weight: 600;
        color: #3b82f6;
        margin: 12px 0 6px 0;
    }
    
    /* Tables */
    table {
        width: 100%;
        border-collapse: collapse;
        margin: 10px 0;
        font-size: 12px;
    }
    
    thead {
        background: #047857;
        color: white;
    }
    
    th {
        padding: 6px 8px;
        text-align: left;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 600;
        background: #f3f4f6;
        color: #1f2937;
        border: 1px solid #d1d5db;
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
    
    tbody tr:nth-child(even) {
        background: #f9fafb;
    }
    
    td {
        padding: 6px 8px;
        color: #374151;
        border: 1px solid #d1d5db;
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
    
    /* Work area headers with pricing */
    .work-area {
        margin-bottom: 30px;
        page-break-inside: avoid;
    }
    
    .work-area-header {
        background: #eff6ff;
        padding: 12px 15px;
        font-weight: 600;
        font-size: 16px;
        border-left: 4px solid #3b82f6;
        margin-bottom: 10px;
        overflow: hidden;
    }
    
    .work-area-header span:first-child {
        float: left;
    }
    
    .work-area-price {
        float: right;
        color: #3b82f6;
        font-weight: 700;
    }
    
    /* Notes/Footer styles */
    .notes {
        margin-top: 30px;
        padding: 20px;
        background: #f9fafb;
        border-left: 4px solid #3b82f6;
    }
    
    .notes h3 {
        margin-top: 0;
        color: #1f2937;
    }
    
    .notes p {
        color: #374151;
        font-size: 13px;
        line-height: 1.7;
    }
    
    /* Totals section - table-based for PDF */
    .totals-section {
        margin-top: 30px;
        width: 100%;
    }
    
    .totals-section table {
        width: 350px;
        margin-left: auto;
        border-collapse: collapse;
    }
    
    .totals-row {
        padding: 10px 0;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .totals-label {
        font-size: 14px;
        color: #6b7280;
        font-weight: 500;
        text-align: left;
        padding: 8px 10px;
    }
    
    .totals-value {
        font-size: 14px;
        color: #111827;
        font-weight: 600;
        text-align: right;
        padding: 8px 10px;
    }
    
    .grand-total {
        background: #3b82f6;
        color: white;
        padding: 16px 20px;
        margin-top: 8px;
    }
    
    .grand-total .totals-label {
        font-size: 16px;
        font-weight: 700;
        color: white;
    }
    
    .grand-total .totals-value {
        font-size: 20px;
        font-weight: 700;
        color: white;
    }
    
    /* Notes and terms */
    .notes-section {
        background: #f9fafb;
        border-left: 4px solid #3b82f6;
        padding: 20px;
        margin: 30px 0;
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
    
    /* Print optimization */
    @media print {
        body {
            padding: 10px;
            font-size: 12px;
        }
        
        .page {
            padding: 10px;
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
        
        h2 {
            margin: 12px 0 6px 0;
        }
        
        .document-title {
            padding: 10px 15px;
            margin: 10px 0;
        }
        
        .two-col-info {
            margin-bottom: 10px;
        }
    }
    
    /* Utility classes */
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
