<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>FINAL_PAYMENT_AGREEMENT_{{ $payslip->user->name }}</title>
    {{-- Using your local AdminLTE assets --}}
    @include('components.adminlte')

    <style>
        @page {
            size: A4;
            margin: 0;
        }
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            background-color: #f3f4f6;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000;
        }
        /* A4 Container */
        .agreement-page {
            width: 210mm;
            height: 297mm;
            padding: 15mm;
            background: #fff;
            margin: 0 auto;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
        }
        /* Inner Border Box */
        .main-content {
            border: 2px solid #000;
            padding: 30px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            position: relative;
        }
        /* Header & Logo */
        .header-section { text-align: center; margin-bottom: 20px; }
        .school-logo { max-height: 80px; margin-bottom: 10px; }
        .school-name { font-size: 32pt; font-weight: 900; text-transform: uppercase; margin: 0; letter-spacing: -1px; }
        .doc-title {
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 2px solid #000;
            display: inline-block;
            padding-bottom: 5px;
            margin-top: 5px;
        }

        /* Typography */
        .statement-text { font-size: 13pt; line-height: 1.4; text-align: justify; margin-bottom: 15px; }
        .highlight-box { background-color: #eee; padding: 2px 8px; font-weight: bold; font-size: 15pt; text-decoration: underline; }

        /* Table Styling */
        .financial-table { width: 100%; border: 2px solid #000; border-collapse: collapse; margin-bottom: 20px; }
        .financial-table th, .financial-table td { border: 1px solid #000; padding: 10px; font-size: 12pt; }
        .financial-table th { background-color: #f5f5f5; text-transform: uppercase; font-size: 10pt; }
        .net-row { background-color: #000; color: #fff; }
        .net-row td { padding: 15px; font-size: 18pt; font-weight: bold; }

        /* Signature Section */
        .footer-grid { display: table; width: 100%; margin-top: auto; }
        .footer-col { display: table-cell; width: 33.33%; vertical-align: bottom; }
        .signature-line { border-top: 2px solid #000; width: 90%; margin-top: 40px; }
        .stamp-zone {
            width: 120px;
            height: 120px;
            border: 2px dashed #000;
            border-radius: 50%;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .legal-footer {
            text-align: center;
            font-size: 8pt;
            color: #666;
            text-transform: uppercase;
            border-top: 1px solid #ccc;
            margin-top: 20px;
            padding-top: 10px;
        }

        /* Print Controls */
        @media print {
            .no-print { display: none !important; }
            body { background: #fff; }
            .agreement-page { box-shadow: none; margin: 0; border: none; }
        }
    </style>
</head>
<body>

    <div class="agreement-page">
        <div class="main-content">

            {{-- 1. INSTITUTIONAL HEADER --}}
            <div class="header-section">
                @if(env('SCHOOL_LOGO_PATH'))
                    <img src="{{ asset(env('SCHOOL_LOGO_PATH')) }}" alt="Logo" class="school-logo">
                @endif
                <h1 class="school-name">{{ env('SCHOOL_NAME', 'KNOWLEDGE PLANET COLLEGE') }}</h1>
                <div class="doc-title">Official Salary Disbursement Advice</div>
                <div style="margin-top: 10px; font-size: 10pt;">
                    {{ env('SCHOOL_ADDRESS') }}<br>
                    Contact: {{ env('SCHOOL_PHONE') }} | Email: {{ env('SCHOOL_EMAIL') }}
                </div>
            </div>

            {{-- 2. FORMAL BINDING STATEMENT --}}
            <div class="statement-text">
                <p>This document constitutes a binding legal acknowledgment of funds received. It confirms that the Employer, <b>{{ env('SCHOOL_NAME') }}</b>, has fulfilled its financial obligations to the Employee for the specified duration.</p>

                <p>I, <b style="text-decoration: underline; font-size: 16pt; text-transform: uppercase;">{{ $payslip->user->name }}</b>,
                   employed as <i>{{ $payslip->user->role }}</i> ({{ $payslip->user->job_type ?? 'Full-Time' }}),
                   hereby acknowledge receipt of my net remuneration for the pay period: <b>{{ $payslip->pay_period }}</b>.</p>

                <p>A total sum of <span class="highlight-box">${{ number_format($payslip->net_salary, 2) }}</span> has been settled in my favor, representing the final balance after all statutory and agreed deductions.</p>
            </div>

            {{-- 3. FINANCIAL AUDIT TABLE --}}
            <table class="financial-table">
                <thead>
                    <tr>
                        <th style="text-align: left;">Description of Entitlements</th>
                        <th style="text-align: right;">Amount (USD)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><b>Contracted Basic Salary</b></td>
                        <td style="text-align: right;"><b>${{ number_format($payslip->base_salary, 2) }}</b></td>
                    </tr>
                    <tr>
                        <td>Allowances</td>
                        <td style="text-align: right;">${{ number_format($payslip->allowances, 2) }}</td>
                    </tr>
                    <tr style="color: #a94442; background-color: #f2dede;">
                        <td><i>Total Deductions (Statutory & Other)</i></td>
                        <td style="text-align: right;">- ${{ number_format($payslip->deductions, 2) }}</td>
                    </tr>
                    <tr class="net-row">
                        <td style="text-align: right; text-transform: uppercase;"><b>Net Total Disbursed</b></td>
                        <td style="text-align: right; border-left: 2px solid white;">
                            <b>${{ number_format($payslip->net_salary, 2) }}</b>
                        </td>
                    </tr>
                </tbody>
            </table>

            {{-- 4. SIGNATURE & VALIDATION --}}
            <div class="footer-grid">
                {{-- Employer --}}
                <div class="footer-col">
                    <p style="font-size: 8pt; font-weight: bold; text-transform: uppercase;">Administrative Authorization:</p>
                    <div class="signature-line"></div>
                    <p style="font-size: 8pt; font-weight: bold; margin-top: 5px;">Authorized Finance Officer</p>
                    <p style="font-size: 8pt; font-style: italic;">Issued: {{ date('d/m/Y') }}</p>
                </div>

                {{-- STAMP --}}
                <div class="footer-col" style="text-align: center;">
                    <div class="stamp-zone">
                        Official<br>Institutional<br>Stamp
                    </div>
                </div>

                {{-- Employee --}}
                <div class="footer-col" style="text-align: right;">
                    <p style="font-size: 8pt; font-weight: bold; text-transform: uppercase;">Employee Acknowledgment:</p>
                    <div style="margin-top: 10px; text-align: left; padding-left: 10%;">
                        <span style="font-size: 7pt; font-weight: bold;">ID / PASSPORT NO:</span>
                        <div style="border-bottom: 1px solid #000; height: 15px; background: #fafafa;"></div>
                    </div>
                    <div class="signature-line" style="margin-left: 10%;"></div>
                    <p style="font-size: 8pt; font-weight: bold; margin-top: 5px;">{{ strtoupper($payslip->user->name) }}</p>
                    <p style="font-size: 8pt; font-style: italic;">Date: ____/____/20__</p>
                </div>
            </div>

            {{-- LEGAL FOOTER --}}
            <div class="legal-footer">
                This document is generated by the {{ config('app.name') }} Management System. Ref: {{ strtoupper(substr(md5($payslip->id), 0, 8)) }} <br>
                Certified as a true record of payment in accordance with Labor Laws.
            </div>
        </div>

        {{-- CONTROLS (Hidden on Print) --}}
        <div class="no-print" style="text-align: center; margin-top: 20px; padding-bottom: 20px;">
            <button onclick="window.print()" class="btn btn-lg btn-warning" style="padding: 15px 40px; font-weight: bold; text-transform: uppercase;">
                <i class="fa fa-print"></i> Print Agreement
            </button>
            <a href="{{ route('payroll.index') }}" class="btn btn-lg btn-default" style="padding: 15px 40px; margin-left: 10px;">Back</a>
        </div>
    </div>

</body>
</html>
