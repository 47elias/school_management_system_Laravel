{{-- resources/views/payroll/print.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FINAL_PAYMENT_AGREEMENT_{{ preg_replace('/[^A-Za-z0-9_]/', '_', $payslip->user->name ?? 'STAFF') }}</title>
    
    <!-- Fonts & FontAwesome -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        serif: ['Times New Roman', 'serif'],
                    }
                }
            }
        }
    </script>

    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; color: #000 !important; }
            .agreement-page { box-shadow: none !important; margin: 0 !important; border: none !important; padding: 12mm !important; }
        }
    </style>
</head>
<body class="bg-slate-100 font-serif text-black antialiased min-h-screen py-8 px-4 sm:px-6">

    <div class="max-w-4xl mx-auto space-y-6">

        {{-- Printable A4 Page Container --}}
        <div class="agreement-page bg-white p-8 sm:p-12 rounded-2xl shadow-lg border border-slate-200 text-black flex flex-col justify-between min-h-[297mm]">
            
            <div class="border-2 border-black p-8 flex-1 flex flex-col justify-between">

                {{-- 1. INSTITUTIONAL HEADER --}}
                <div>
                    <div class="text-center pb-6 border-b-2 border-black mb-6">
                        @if(env('SCHOOL_LOGO_PATH'))
                            <img src="{{ asset(env('SCHOOL_LOGO_PATH')) }}" alt="Logo" class="h-16 mx-auto mb-3 object-contain">
                        @endif
                        <h1 class="text-3xl font-black uppercase tracking-tight font-serif">{{ env('SCHOOL_NAME', 'KNOWLEDGE PLANET COLLEGE') }}</h1>
                        <div class="inline-block mt-2 px-4 py-1 text-sm font-bold uppercase tracking-wider border-b-2 border-black font-sans">
                            Official Salary Disbursement Advice
                        </div>
                        <div class="mt-3 text-xs font-sans text-slate-700 leading-tight">
                            {{ env('SCHOOL_ADDRESS') }}<br>
                            Contact: {{ env('SCHOOL_PHONE') }} | Email: {{ env('SCHOOL_EMAIL') }}
                        </div>
                    </div>

                    {{-- 2. FORMAL BINDING STATEMENT --}}
                    <div class="text-sm font-serif leading-relaxed space-y-4 mb-6">
                        <p class="text-justify">
                            This document constitutes a binding legal acknowledgment of funds received. It confirms that the Employer, 
                            <strong>{{ env('SCHOOL_NAME', 'Knowledge Planet College') }}</strong>, has fulfilled its financial obligations to the Employee for the specified duration.
                        </p>

                        <p class="text-justify">
                            I, <strong class="underline text-base uppercase font-bold">{{ $payslip->user->name ?? 'Staff Member' }}</strong>,
                            employed as <em>{{ $payslip->user->role ?? 'Employee' }}</em> ({{ $payslip->user->job_type ?? 'Full-Time' }}),
                            hereby acknowledge receipt of my net remuneration for the pay period: <strong>{{ $payslip->pay_period }}</strong>.
                        </p>

                        <p class="text-justify">
                            A total sum of <span class="bg-slate-200 px-3 py-1 font-bold text-base underline font-mono">${{ number_format($payslip->net_salary, 2) }}</span> 
                            has been settled in my favor, representing the final balance after all statutory and agreed deductions.
                        </p>
                    </div>

                    {{-- 3. FINANCIAL AUDIT TABLE --}}
                    <div class="mb-6">
                        <table class="w-full border-2 border-black text-xs font-serif divide-y divide-black">
                            <thead class="bg-slate-100 font-sans uppercase">
                                <tr>
                                    <th class="p-3 text-left font-bold border-r border-black">Description of Entitlements</th>
                                    <th class="p-3 text-right font-bold">Amount (USD)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-black">
                                <tr>
                                    <td class="p-3 font-bold border-r border-black">Contracted Basic Salary</td>
                                    <td class="p-3 text-right font-mono font-bold">${{ number_format($payslip->base_salary, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="p-3 border-r border-black">Allowances</td>
                                    <td class="p-3 text-right font-mono">${{ number_format($payslip->allowances, 2) }}</td>
                                </tr>
                                <tr class="bg-rose-50 text-rose-900">
                                    <td class="p-3 italic border-r border-black">Total Deductions (Statutory & Other)</td>
                                    <td class="p-3 text-right font-mono">- ${{ number_format($payslip->deductions, 2) }}</td>
                                </tr>
                                <tr class="bg-black text-white font-sans">
                                    <td class="p-4 text-right uppercase font-bold text-sm border-r border-white">Net Total Disbursed</td>
                                    <td class="p-4 text-right font-mono text-xl font-black">${{ number_format($payslip->net_salary, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- 4. SIGNATURE & VALIDATION --}}
                <div class="mt-8">
                    <div class="grid grid-cols-3 gap-6 items-end font-sans text-xs">
                        
                        {{-- Employer Signature --}}
                        <div>
                            <p class="font-bold uppercase text-[10px] text-slate-700">Administrative Authorization:</p>
                            <div class="border-b-2 border-black w-full mt-10 mb-1"></div>
                            <p class="font-bold">Authorized Finance Officer</p>
                            <p class="text-[10px] text-slate-500 italic">Issued: {{ date('d/m/Y') }}</p>
                        </div>

                        {{-- Official Stamp Zone --}}
                        <div class="text-center">
                            <div class="w-28 h-28 border-2 border-dashed border-black rounded-full mx-auto flex items-center justify-center text-[10px] font-bold uppercase text-center leading-tight">
                                Official<br>Institutional<br>Stamp
                            </div>
                        </div>

                        {{-- Employee Signature --}}
                        <div>
                            <p class="font-bold uppercase text-[10px] text-slate-700 text-right">Employee Acknowledgment:</p>
                            <div class="mt-4 text-left pl-4">
                                <span class="text-[9px] font-bold uppercase block text-slate-600">ID / PASSPORT NO:</span>
                                <div class="border-b border-black h-4 bg-slate-50 w-full mt-0.5"></div>
                            </div>
                            <div class="border-b-2 border-black w-full mt-6 mb-1"></div>
                            <p class="font-bold uppercase text-right">{{ $payslip->user->name ?? 'Staff Member' }}</p>
                            <p class="text-[10px] text-slate-500 italic text-right">Date: ____/____/20__</p>
                        </div>

                    </div>

                    {{-- LEGAL FOOTER --}}
                    <div class="mt-8 pt-4 border-t border-slate-300 text-center font-sans text-[10px] text-slate-500 uppercase leading-tight">
                        This document is generated by the {{ config('app.name', 'SMS') }} Management System. Ref: {{ strtoupper(substr(md5($payslip->id ?? time()), 0, 8)) }} <br>
                        Certified as a true record of payment in accordance with Labor Laws.
                    </div>
                </div>

            </div>

        </div>

        {{-- CONTROLS (Hidden on Print) --}}
        <div class="no-print text-center flex items-center justify-center space-x-4 pt-4">
            <button onclick="window.print()" 
                    class="px-8 py-3.5 rounded-2xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs uppercase tracking-wider shadow-md transition-all flex items-center">
                <i class="fa-solid fa-print mr-2 text-sm"></i> Print Agreement
            </button>
            <a href="{{ Route::has('payroll.index') ? route('payroll.index') : url('/payroll') }}" 
               class="px-8 py-3.5 rounded-2xl bg-white dark:bg-slate-800 hover:bg-slate-100 text-slate-700 dark:text-slate-200 font-bold text-xs uppercase tracking-wider border border-slate-200 dark:border-slate-700 transition-all">
                Back to Payroll
            </a>
        </div>

    </div>

</body>
</html>