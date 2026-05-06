<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Salary Slip - {{ $salaryPayment->slip_no ?? 'Slip' }}</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 30px;
            background: #f1f5f9;
            font-family: Arial, sans-serif;
            color: #0f172a;
        }

        .print-actions {
            max-width: 850px;
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
            color: #fff;
        }

        .btn-back {
            background: #fff;
            color: #0f172a;
            border: 1px solid #e2e8f0;
        }

        .slip-wrap {
            max-width: 850px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.12);
        }

        .slip-header {
            padding: 28px;
            background: linear-gradient(135deg, #0f172a, #1e293b);
            color: #fff;
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }

        .brand-title {
            margin: 0;
            font-size: 28px;
            font-weight: 900;
        }

        .brand-subtitle {
            margin: 8px 0 0;
            color: #cbd5e1;
            font-size: 13px;
            line-height: 1.6;
        }

        .slip-title {
            text-align: right;
        }

        .slip-title h2 {
            margin: 0;
            font-size: 30px;
            font-weight: 900;
        }

        .slip-no {
            margin-top: 8px;
            display: inline-block;
            padding: 7px 12px;
            border-radius: 999px;
            background: rgba(255,255,255,.12);
            font-size: 13px;
            font-weight: 700;
        }

        .slip-body {
            padding: 28px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            margin-bottom: 24px;
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
        }

        .info-line {
            margin: 8px 0;
            font-size: 14px;
            color: #475569;
        }

        .info-line strong {
            min-width: 120px;
            display: inline-block;
            color: #0f172a;
        }

        .amount-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            overflow: hidden;
        }

        .amount-table th,
        .amount-table td {
            padding: 14px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }

        .amount-table th {
            background: #f8fafc;
            text-align: left;
            color: #334155;
            text-transform: uppercase;
            font-size: 12px;
        }

        .amount-table tr:last-child td {
            border-bottom: none;
        }

        .text-right { text-align: right; }

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

        .summary-row.total {
            background: #0f172a;
            color: #fff;
            font-weight: 900;
            font-size: 17px;
        }

        .paid { color: #166534; font-weight: 900; }
        .due { color: #991b1b; font-weight: 900; }

        .footer {
            padding: 24px 28px 30px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 30px;
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

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .print-actions {
                display: none;
            }

            .slip-wrap {
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

<div class="slip-wrap">

    <div class="slip-header">
        <div>
            <h1 class="brand-title">{{ trans('panel.site_title') }}</h1>
            <p class="brand-subtitle">
                {{ $salaryPayment->branch->name ?? 'Coaching Institute' }}<br>
                Salary / Payroll Department
            </p>
        </div>

        <div class="slip-title">
            <h2>SALARY SLIP</h2>
            <span class="slip-no">{{ $salaryPayment->slip_no ?? '-' }}</span>
        </div>
    </div>

    <div class="slip-body">

        <div class="info-grid">
            <div class="info-card">
                <p class="info-title">Employee Details</p>

                <p class="info-line">
                    <strong>Name:</strong>
                    {{ $salaryPayment->employee_name }}
                </p>

                <p class="info-line">
                    <strong>Type:</strong>
                    {{ ucfirst($salaryPayment->employee_type) }}
                </p>

                <p class="info-line">
                    <strong>Branch:</strong>
                    {{ $salaryPayment->branch->name ?? '-' }}
                </p>

                <p class="info-line">
                    <strong>Month:</strong>
                    {{ $salaryPayment->salary_month ?? '-' }}
                </p>
            </div>

            <div class="info-card">
                <p class="info-title">Payment Details</p>

                <p class="info-line">
                    <strong>Slip No:</strong>
                    {{ $salaryPayment->slip_no ?? '-' }}
                </p>

                <p class="info-line">
                    <strong>Date:</strong>
                    {{ $salaryPayment->payment_date ? \Carbon\Carbon::parse($salaryPayment->payment_date)->format('d M Y') : '-' }}
                </p>

                <p class="info-line">
                    <strong>Mode:</strong>
                    {{ ucwords(str_replace('_', ' ', $salaryPayment->payment_mode)) }}
                </p>

                <p class="info-line">
                    <strong>Paid By:</strong>
                    {{ $salaryPayment->paidBy->name ?? '-' }}
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
                    <td>Basic Salary</td>
                    <td class="text-right">₹{{ number_format($salaryPayment->basic_salary, 2) }}</td>
                </tr>

                <tr>
                    <td>Bonus</td>
                    <td class="text-right">₹{{ number_format($salaryPayment->bonus, 2) }}</td>
                </tr>

                <tr>
                    <td>Deduction</td>
                    <td class="text-right">₹{{ number_format($salaryPayment->deduction, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="summary-box">
            <div class="summary-row">
                <span>Net Salary</span>
                <strong>₹{{ number_format($salaryPayment->net_salary, 2) }}</strong>
            </div>

            <div class="summary-row">
                <span>Paid Amount</span>
                <strong class="paid">₹{{ number_format($salaryPayment->paid_amount, 2) }}</strong>
            </div>

            <div class="summary-row">
                <span>Due Amount</span>
                <strong class="due">₹{{ number_format($salaryPayment->due_amount, 2) }}</strong>
            </div>

            <div class="summary-row total">
                <span>Received</span>
                <strong>₹{{ number_format($salaryPayment->paid_amount, 2) }}</strong>
            </div>
        </div>

    </div>

    <div class="footer">
        <div class="note">
            <strong>Note:</strong><br>
            This is a computer-generated salary slip. Please keep it for your records.
        </div>

        <div class="signature">
            <div class="signature-line"></div>
            Authorized Signature
        </div>
    </div>

</div>

</body>
</html>