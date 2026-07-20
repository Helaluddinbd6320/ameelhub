<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>Milestone Receipt — {{ $deal->uuid }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #1f2937; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #0f766e; padding-bottom: 10px; }
        .header h1 { color: #0f766e; margin: 0; font-size: 20px; }
        .header p { margin: 2px 0; font-size: 11px; color: #6b7280; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        td, th { padding: 6px 8px; border: 1px solid #d1d5db; text-align: left; font-size: 11px; }
        th { background: #f0fdfa; width: 35%; }
        .amount-box { margin-top: 16px; padding: 12px; background: #f0fdfa; border: 1px solid #0f766e; border-radius: 4px; }
        .amount-box table td { border: none; padding: 4px 0; }
        .total-row td { font-weight: bold; font-size: 13px; border-top: 1px solid #0f766e; }
        .footer { margin-top: 30px; font-size: 10px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>AmeelHub — Milestone Receipt</h1>
        <p>Chapai International | Medina, Saudi Arabia | ameelhub.com</p>
    </div>

    <table>
        <tr><th>Deal ID</th><td>{{ $deal->uuid }}</td></tr>
        <tr><th>Worker</th><td>{{ $deal->worker->full_name_bn ?? '-' }} ({{ $deal->worker->full_name_en ?? '-' }})</td></tr>
        <tr><th>Agent</th><td>{{ $deal->agent->name ?? '-' }}</td></tr>
        <tr><th>Job Post</th><td>{{ $deal->jobPost->job_title ?? '-' }}</td></tr>
        <tr><th>Milestone</th><td>#{{ $milestone->milestone_number }} — {{ $milestone->title }}</td></tr>
        <tr><th>Percentage</th><td>{{ $milestone->percentage }}%</td></tr>
        <tr><th>Released By</th><td>{{ $milestone->releasedBy->name ?? '-' }}</td></tr>
        <tr><th>Released At</th><td>{{ optional($milestone->admin_released_at)->format('d M Y, h:i A') }}</td></tr>
    </table>

    <div class="amount-box">
        <table>
            <tr><td>মোট মাইলস্টোন পরিমাণ (Total Amount)</td><td style="text-align:right">{{ number_format($milestone->amount_sar, 2) }} SAR</td></tr>
            <tr><td>Chapai কমিশন ({{ $deal->chapai_commission_pct }}%)</td><td style="text-align:right">- {{ number_format($milestone->commission_sar, 2) }} SAR</td></tr>
            <tr class="total-row"><td>Agent যা পাবে</td><td style="text-align:right">{{ number_format($milestone->agent_receives_sar, 2) }} SAR</td></tr>
        </table>
    </div>

    <div class="footer">
        এই রশিদটি স্বয়ংক্রিয়ভাবে তৈরি — AmeelHub Escrow System<br>
        License No. 0016205
    </div>
</body>
</html>