<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice - {{ $feePayment->receipt_no ?? 'Receipt' }}</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px;
            background: #f1f5f9;
            font-family: Arial, sans-serif;
            color: #0f172a;
        }

        .invoice-wrap {
            max-width: 900px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.12);
        }

        .invoice-header {
            padding: 30px;
            background: linear-gradient(135deg, #0f172a, #1e293b);
            color: #ffffff;
            display: flex;
            justify-content: space-between;
            gap: 20px;
            align-items: flex-start;
        }

        .brand-title {
            margin: 0;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .brand-subtitle {
            margin: 8px 0 0;
            font-size: 13px;
            color: #cbd5e1;
            line-height: 1.6;
        }

        .invoice-title-box {
            text-align: right;
        }

        .invoice-title {
            margin: 0;
            font-size: 32px;
            font-weight: 900;
            letter-spacing: 1px;
        }

        .invoice-no {
            margin-top: 8px;
            display: inline-block;
            padding: 7px 12px;
            border-radius: 999px;
            background: rgba(255,255,255,0.12);
            font-size: 13px;
            font-weight: 700;
        }

        .invoice-body {
            padding: 30px;
        }

        .top-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 28px;
        }

        .info-card {
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 18px;
            background: #f8fafc;
        }

        .info-title {
            margin: 0 0 12px;
            font-size: 14px;
            font-weight: 800;
            color: #334155;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-line {
            margin: 7px 0;
            font-size: 14px;
            color: #475569;
        }

        .info-line strong {
            color: #0f172a;
            min-width: 110px;
            display: inline-block;
        }

        .amount-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .amount-table th {
            background: #f8fafc;
            color: #334155;
            text-align: left;
            padding: 14px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            border-bottom: 1px solid #e2e8f0;
        }

        .amount-table td {
            padding: 14px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
            color: #475569;
        }

        .amount-table tr:last-child td {
            border-bottom: none;
        }

        .text-right {
            text-align: right;
        }

        .summary-box {
            max-width: 360px;
            margin-left: auto;
            margin-top: 24px;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            overflow: hidden;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 13px 16px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }

        .summary-row:last-child {
            border-bottom: none;
        }

        .summary-row strong {
            color: #0f172a;
        }

        .summary-row.total {
            background: #0f172a;
            color: #ffffff;
            font-size: 17px;
            font-weight: 800;
        }

        .summary-row.total strong {
            color: #ffffff;
        }

        .paid {
            color: #166534;
            font-weight: 800;
        }

        .due {
            color: #991b1b;
            font-weight: 800;
        }

        .status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .status-paid {
            background: #dcfce7;
            color: #166534;
        }

        .status-partial {
            background: #fef3c7;
            color: #92400e;
        }

        .status-due {
            background: #f1f5f9;
            color: #475569;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        .remarks-box {
            margin-top: 24px;
            padding: 16px;
            border-radius: 16px;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            font-size: 14px;
            color: #475569;
        }

        .invoice-footer {
            padding: 24px 30px 30px;
            display: flex;
            justify-content: space-between;
            gap: 30px;
            align-items: flex-end;
        }

        .note {
            font-size: 13px;
            color: #64748b;
            line-height: 1.6;
        }

        .signature {
            text-align: center;
            min-width: 220px;
        }

        .signature-line {
            height: 1px;
            background: #0f172a;
            margin-bottom: 8px;
        }

        .print-actions {
            max-width: 900px;
            margin: 0 auto 16px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn {
            border: none;
            cursor: pointer;
            padding: 10px 16px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 14px;
        }

        .btn-print {
            background: #0f172a;
            color: #ffffff;
        }

        .btn-back {
            background: #ffffff;
            color: #0f172a;
            border: 1px solid #e2e8f0;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }

            .print-actions {
                display: none;
            }

            .invoice-wrap {
                max-width: 100%;
                box-shadow: none;
                border-radius: 0;
            }

            @page {
                size: A4;
                margin: 12mm;
            }
        }
    </style>
</head>

<body>

<div class="print-actions">
    <button type="button" class="btn btn-back" onclick="window.close()">Close</button>
    <button type="button" class="btn btn-print" onclick="window.print()">Print / Save PDF</button>
</div>

<div class="invoice-wrap">

    <div class="invoice-header">
        <div>
            <h1 class="brand-title">{{ trans('panel.site_title') }}</h1>
            <p class="brand-subtitle">
                {{ $feePayment->branch->name ?? 'Coaching Institute' }}<br>
                {{ $feePayment->branch->address ?? 'Branch Address' }}<br>
                Phone: {{ $feePayment->branch->phone ?? '-' }}
            </p>
        </div>

        <div class="invoice-title-box">
            <h2 class="invoice-title">INVOICE</h2>
            <span class="invoice-no">{{ $feePayment->receipt_no ?? '-' }}</span>
        </div>
    </div>

    <div class="invoice-body">

        <div class="top-grid">
            <div class="info-card">
                <p class="info-title">Student Details</p>

                <p class="info-line">
                    <strong>Name:</strong>
                    {{ $feePayment->student->user->name ?? '-' }}
                </p>

                <p class="info-line">
                    <strong>Student Code:</strong>
                    {{ $feePayment->student->student_code ?? '-' }}
                </p>

                <p class="info-line">
                    <strong>Phone:</strong>
                    {{ $feePayment->student->phone ?? '-' }}
                </p>

                <p class="info-line">
                    <strong>Course:</strong>
                    {{ $feePayment->course->name ?? '-' }}
                </p>

                <p class="info-line">
                    <strong>Batch:</strong>
                    {{ $feePayment->batch->name ?? '-' }}
                </p>
            </div>

            <div class="info-card">
                <p class="info-title">Payment Details</p>

                <p class="info-line">
                    <strong>Receipt No:</strong>
                    {{ $feePayment->receipt_no ?? '-' }}
                </p>

                <p class="info-line">
                    <strong>Date:</strong>
                    {{ $feePayment->payment_date ? \Carbon\Carbon::parse($feePayment->payment_date)->format('d M Y') : '-' }}
                </p>

                <p class="info-line">
                    <strong>Mode:</strong>
                    {{ ucwords(str_replace('_', ' ', $feePayment->payment_mode)) }}
                </p>

                <p class="info-line">
                    <strong>Collected By:</strong>
                    {{ $feePayment->collectedBy->name ?? '-' }}
                </p>

                <p class="info-line">
                    <strong>Status:</strong>
                    <span class="status status-{{ $feePayment->payment_status }}">
                        {{ ucfirst($feePayment->payment_status) }}
                    </span>
                </p>
            </div>
        </div>

        <table class="amount-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>
                        Course Fee
                        <br>
                        <small>
                            {{ $feePayment->course->name ?? 'Course' }}
                            {{ $feePayment->batch ? ' - ' . $feePayment->batch->name : '' }}
                        </small>
                    </td>
                    <td class="text-right">
                        ₹{{ number_format($feePayment->total_fee, 2) }}
                    </td>
                </tr>

                <tr>
                    <td>Discount</td>
                    <td class="text-right">
                        ₹{{ number_format($feePayment->discount, 2) }}
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="summary-box">
            <div class="summary-row">
                <span>Total Fee</span>
                <strong>₹{{ number_format($feePayment->total_fee, 2) }}</strong>
            </div>

            <div class="summary-row">
                <span>Discount</span>
                <strong>₹{{ number_format($feePayment->discount, 2) }}</strong>
            </div>

            <div class="summary-row">
                <span>Payable Amount</span>
                <strong>₹{{ number_format($feePayment->payable_amount, 2) }}</strong>
            </div>

            <div class="summary-row">
                <span>Paid Amount</span>
                <strong class="paid">₹{{ number_format($feePayment->paid_amount, 2) }}</strong>
            </div>

            <div class="summary-row">
                <span>Due Amount</span>
                <strong class="due">₹{{ number_format($feePayment->due_amount, 2) }}</strong>
            </div>

            <div class="summary-row total">
                <span>Received</span>
                <strong>₹{{ number_format($feePayment->paid_amount, 2) }}</strong>
            </div>
        </div>

        @if($feePayment->remarks)
            <div class="remarks-box">
                <strong>Remarks:</strong>
                {{ $feePayment->remarks }}
            </div>
        @endif

    </div>

    <div class="invoice-footer">
        <div class="note">
            <strong>Note:</strong><br>
            This is a computer-generated fee receipt/invoice. Please keep it for your records.
        </div>

        <div class="signature">
            <div class="signature-line"></div>
            Authorized Signature
        </div>
    </div>

</div>

</body>
</html>