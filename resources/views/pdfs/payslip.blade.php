<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $locale == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $locale == 'ar' ? 'قسيمة الراتب' : 'Payslip' }} - {{ $record->payrollPeriod->name }}</title>
    <style>
        @page { margin: 2cm; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #667eea;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-name {
            font-size: 24pt;
            font-weight: bold;
            color: #1a237e;
            margin-bottom: 5px;
        }
        .document-title {
            font-size: 16pt;
            color: #666;
            text-transform: uppercase;
        }
        .info-section {
            width: 100%;
            margin-bottom: 30px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 5px;
            vertical-align: top;
        }
        .label {
            font-weight: bold;
            color: #555;
            width: 150px;
        }
        .value {
            color: #000;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .summary-table th {
            background-color: #f5f7ff;
            color: #1a237e;
            padding: 10px;
            text-align: left;
            border-bottom: 2px solid #667eea;
        }
        .summary-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #eee;
        }
        .summary-table .amount {
            text-align: right;
            font-family: 'Courier', monospace;
        }
        .summary-table .total-row td {
            font-weight: bold;
            background-color: #fafafa;
            border-top: 2px solid #333;
        }
        .section-title {
            font-size: 14pt;
            font-weight: bold;
            color: #1a237e;
            margin-bottom: 10px;
            border-left: 4px solid #667eea;
            padding-left: 10px;
        }
        .footer {
            margin-top: 50px;
            font-size: 8pt;
            color: #999;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        .rtl { direction: rtl; }
        .ltr { direction: ltr; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        
        [dir="rtl"] .section-title {
            border-left: none;
            border-right: 4px solid #667eea;
            padding-left: 0;
            padding-right: 10px;
        }
        [dir="rtl"] .summary-table th { text-align: right; }
        [dir="rtl"] .summary-table .amount { text-align: left; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">HRM Platform</div>
        <div class="document-title">{{ $locale == 'ar' ? 'قسيمة راتب الموظف' : 'Employee Payslip' }}</div>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td class="label">{{ $locale == 'ar' ? 'اسم الموظف:' : 'Employee Name:' }}</td>
                <td class="value">{{ $record->employee->full_name }}</td>
                <td class="label">{{ $locale == 'ar' ? 'الرقم الوظيفي:' : 'Employee ID:' }}</td>
                <td class="value">{{ $record->employee->employee_code }}</td>
            </tr>
            <tr>
                <td class="label">{{ $locale == 'ar' ? 'القسم:' : 'Department:' }}</td>
                <td class="value">{{ $record->employee->department }}</td>
                <td class="label">{{ $locale == 'ar' ? 'الفترة:' : 'Period:' }}</td>
                <td class="value">{{ $record->payrollPeriod->name }}</td>
            </tr>
            <tr>
                <td class="label">{{ $locale == 'ar' ? 'تاريخ التعيين:' : 'Hire Date:' }}</td>
                <td class="value">{{ $record->employee->hire_date }}</td>
                <td class="label">{{ $locale == 'ar' ? 'أيام العمل:' : 'Days Worked:' }}</td>
                <td class="value">{{ $record->days_worked }}</td>
            </tr>
        </table>
    </div>

    <div class="section-title">{{ $locale == 'ar' ? 'تفاصيل المستحقات' : 'Earnings Details' }}</div>
    <table class="summary-table">
        <thead>
            <tr>
                <th>{{ $locale == 'ar' ? 'الوصف' : 'Description' }}</th>
                <th class="amount">{{ $locale == 'ar' ? 'المبلغ' : 'Amount' }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $locale == 'ar' ? 'الراتب الأساسي' : 'Basic Salary' }}</td>
                <td class="amount">{{ number_format($record->basic_salary, 2) }}</td>
            </tr>
            @if($record->allowances > 0)
            <tr>
                <td>{{ $locale == 'ar' ? 'البدلات' : 'Allowances' }}</td>
                <td class="amount">{{ number_format($record->allowances, 2) }}</td>
            </tr>
            @endif
            @if($record->bonuses > 0)
            <tr>
                <td>{{ $locale == 'ar' ? 'المكافآت' : 'Bonuses' }}</td>
                <td class="amount">{{ number_format($record->bonuses, 2) }}</td>
            </tr>
            @endif
            @if($record->overtime_pay > 0)
            <tr>
                <td>{{ $locale == 'ar' ? 'العمل الإضافي' : 'Overtime' }}</td>
                <td class="amount">{{ number_format($record->overtime_pay, 2) }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td>{{ $locale == 'ar' ? 'إجمالي المستحقات' : 'Total Earnings' }}</td>
                <td class="amount">{{ number_format($record->gross_salary, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">{{ $locale == 'ar' ? 'تفاصيل الاستقطاعات' : 'Deductions Details' }}</div>
    <table class="summary-table">
        <thead>
            <tr>
                <th>{{ $locale == 'ar' ? 'الوصف' : 'Description' }}</th>
                <th class="amount">{{ $locale == 'ar' ? 'المبلغ' : 'Amount' }}</th>
            </tr>
        </thead>
        <tbody>
            @if($record->tax_deduction > 0)
            <tr>
                <td>{{ $locale == 'ar' ? 'الضرائب' : 'Income Tax' }}</td>
                <td class="amount">{{ number_format($record->tax_deduction, 2) }}</td>
            </tr>
            @endif
            @if($record->insurance_deduction > 0)
            <tr>
                <td>{{ $locale == 'ar' ? 'التأمينات الاجتماعية' : 'Social Insurance' }}</td>
                <td class="amount">{{ number_format($record->insurance_deduction, 2) }}</td>
            </tr>
            @endif
            @if($record->other_deductions > 0)
            <tr>
                <td>{{ $locale == 'ar' ? 'استقطاعات أخرى' : 'Other Deductions' }}</td>
                <td class="amount">{{ number_format($record->other_deductions, 2) }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td>{{ $locale == 'ar' ? 'إجمالي الاستقطاعات' : 'Total Deductions' }}</td>
                <td class="amount">{{ number_format($record->total_deductions, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 40px; border: 2px solid #1a237e; padding: 15px; background-color: #f5f7ff;">
        <table style="width: 100%;">
            <tr>
                <td style="font-size: 14pt; font-weight: bold; color: #1a237e;">
                    {{ $locale == 'ar' ? 'صافي الراتب المستحق' : 'NET SALARY PAYABLE' }}
                </td>
                <td style="font-size: 18pt; font-weight: bold; color: #1a237e; text-align: right;" class="amount">
                    {{ number_format($record->net_salary, 2) }}
                    <span style="font-size: 10pt;">{{ $locale == 'ar' ? 'ج.م' : 'EGP' }}</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>{{ $locale == 'ar' ? 'هذه وثيقة تم إنشاؤها آلياً ولا تتطلب توقيعاً.' : 'This is a computer generated document and does not require a signature.' }}</p>
        <p>&copy; {{ date('Y') }} HRM Platform. {{ $locale == 'ar' ? 'جميع الحقوق محفوظة.' : 'All rights reserved.' }}</p>
    </div>
</body>
</html>
