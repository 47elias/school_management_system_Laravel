{{-- resources/views/payroll/index.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Staff Payroll | ' . env('SCHOOL_ACRONYM', 'SMS'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Staff Payroll</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Finance control, salary processing & disbursement records</p>
        </div>
    </div>
@endsection

@section('content')
    <div x-data="{
            baseSalary: 0,
            allowances: 0,
            deductions: 0,
            get netSalary() {
                let base = parseFloat(this.baseSalary) || 0;
                let allow = parseFloat(this.allowances) || 0;
                let ded = parseFloat(this.deductions) || 0;
                return (base + allow - ded).toFixed(2);
            },
            onStaffChange(event) {
                const selectedOption = event.target.options[event.target.selectedIndex];
                const salary = selectedOption.getAttribute('data-salary');
                this.baseSalary = salary ? parseFloat(salary) : 0;
            }
         }" 
         class="space-y-6">

        {{-- Financial Summary Row --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            
            {{-- Total Salaries Paid --}}
            <div class="bg-gradient-to-br from-rose-500 to-pink-600 rounded-2xl p-6 text-white shadow-sm relative overflow-hidden">
                <div class="relative z-10">
                    <span class="text-xs font-bold uppercase tracking-wider opacity-80 block">Total Salaries Paid</span>
                    <h3 class="text-3xl font-black mt-2 font-mono tracking-tight">${{ number_format($totalExpenses, 2) }}</h3>
                </div>
                <i class="fa-solid fa-money-bill-wave absolute right-4 bottom-2 text-6xl opacity-15"></i>
            </div>

            {{-- Available Fund Balance --}}
            <div class="bg-gradient-to-br from-indigo-500 to-blue-600 rounded-2xl p-6 text-white shadow-sm relative overflow-hidden">
                <div class="relative z-10">
                    <span class="text-xs font-bold uppercase tracking-wider opacity-80 block">Available Fund Balance</span>
                    <h3 class="text-3xl font-black mt-2 font-mono tracking-tight">${{ number_format($schoolBalance, 2) }}</h3>
                </div>
                <i class="fa-solid fa-briefcase absolute right-4 bottom-2 text-6xl opacity-15"></i>
            </div>

        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-200 flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-lg"></i>
                    <p class="text-xs font-medium">{{ session('success') }}</p>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-200">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/50 text-rose-800 dark:text-rose-200 shadow-sm space-y-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-triangle-exclamation text-rose-600 dark:text-rose-400 text-lg"></i>
                        <h4 class="text-xs font-bold uppercase tracking-wider">Please correct the form errors:</h4>
                    </div>
                    <button type="button" onclick="this.parentElement.parentElement.remove()" class="text-rose-600 dark:text-rose-400 hover:text-rose-800 dark:hover:text-rose-200">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>
                <ul class="list-disc list-inside text-xs font-medium space-y-1 pl-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Main Layout Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- Left Side: New Entry Form --}}
            <div class="lg:col-span-5">
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
                    <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/80">
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                            <i class="fa-solid fa-circle-plus text-indigo-500 mr-2.5"></i> Process New Payment
                        </h3>
                    </div>

                    <form action="{{ Route::has('payroll.store') ? route('payroll.store') : url('/payroll') }}" method="POST" id="payrollForm" class="p-6 space-y-5">
                        @csrf

                        {{-- Section 1: Staff Details --}}
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 mb-3 pb-1 border-b border-slate-200 dark:border-slate-700/60 flex items-center">
                                <i class="fa-solid fa-user mr-2"></i> 1. Staff Details
                            </h4>
                            <div class="space-y-4">
                                <div>
                                    <label for="teacher_id" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                        Select Staff Member <span class="text-rose-500">*</span>
                                    </label>
                                    <select name="user_id" id="teacher_id" @change="onStaffChange($event)" required 
                                            class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <option value="">-- Search Teacher / Staff --</option>
                                        @foreach($teachers as $teacher)
                                            <option value="{{ $teacher->id }}"
                                                    data-salary="{{ $teacher->base_salary }}"
                                                    data-empno="{{ $teacher->employee_number }}">
                                                {{ $teacher->name }} ({{ $teacher->employee_number ?? 'No ID' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                        Pay Period (Month/Year) <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="relative rounded-xl">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                            <i class="fa-regular fa-calendar-check"></i>
                                        </div>
                                        <input type="text" name="pay_period" placeholder="e.g. February 2026" required
                                               class="w-full pl-10 pr-3.5 py-2.5 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Section 2: Salary Breakdown --}}
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 mb-3 pb-1 border-b border-slate-200 dark:border-slate-700/60 flex items-center">
                                <i class="fa-solid fa-calculator mr-2"></i> 2. Salary Breakdown
                            </h4>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                        Base Salary <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="relative rounded-xl">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 font-bold">
                                            $
                                        </div>
                                        <input type="number" name="base_salary" id="base_salary" step="0.01" x-model="baseSalary" required placeholder="0.00"
                                               class="w-full pl-8 pr-3.5 py-2.5 text-xs font-mono font-bold rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 mb-1.5">
                                            Allowances (+)
                                        </label>
                                        <input type="number" name="allowances" id="allowances" step="0.01" x-model="allowances" placeholder="0.00"
                                               class="w-full px-3.5 py-2.5 text-xs font-mono font-bold rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-emerald-600 dark:text-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400 mb-1.5">
                                            Deductions (-)
                                        </label>
                                        <input type="number" name="deductions" id="deductions" step="0.01" x-model="deductions" placeholder="0.00"
                                               class="w-full px-3.5 py-2.5 text-xs font-mono font-bold rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-rose-600 dark:text-rose-400 focus:outline-none focus:ring-2 focus:ring-rose-500">
                                    </div>
                                </div>

                                {{-- Net Preview Box --}}
                                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700/60 flex items-center justify-between">
                                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Calculated Net:</span>
                                    <span class="text-2xl font-black font-mono"
                                          :class="parseFloat(netSalary) < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-indigo-600 dark:text-indigo-400'">
                                        $<span x-text="netSalary">0.00</span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Section 3: Disbursement Info --}}
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 mb-3 pb-1 border-b border-slate-200 dark:border-slate-700/60 flex items-center">
                                <i class="fa-solid fa-circle-info mr-2"></i> 3. Disbursement Info
                            </h4>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                        Date of Payment <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required
                                           class="w-full px-3.5 py-2.5 text-xs font-medium rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                        Internal Remarks
                                    </label>
                                    <textarea name="remarks" rows="2" placeholder="Bank transfer, cash, check reference, etc."
                                              class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 leading-relaxed"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit" 
                                    class="w-full py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs uppercase tracking-wider transition-all shadow-sm flex items-center justify-center">
                                <i class="fa-solid fa-file-invoice mr-2"></i> GENERATE PAYSLIP
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Right Side: History Table --}}
            <div class="lg:col-span-7">
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
                    <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/80">
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                            <i class="fa-solid fa-clock-rotate-left text-indigo-500 mr-2.5"></i> Recent Transactions
                        </h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700/60 text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-800/80">
                                <tr>
                                    <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Staff Information</th>
                                    <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Period</th>
                                    <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Net Salary</th>
                                    <th scope="col" class="px-6 py-3.5 text-right font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-100 dark:divide-slate-700/60">
                                @forelse($payslips as $p)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap font-mono text-slate-500 dark:text-slate-400">
                                        {{ \Carbon\Carbon::parse($p->payment_date)->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <strong class="text-xs font-bold text-slate-800 dark:text-slate-100 block">{{ $p->user->name ?? 'Staff Member' }}</strong>
                                        <span class="text-[11px] font-mono text-slate-400 flex items-center mt-0.5">
                                            <i class="fa-solid fa-id-card mr-1"></i> {{ $p->user->employee_number ?? 'No ID' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600">
                                            {{ $p->pay_period }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap font-mono font-black text-emerald-600 dark:text-emerald-400 text-sm">
                                        ${{ number_format($p->net_salary, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right space-x-1">
                                        <a href="{{ Route::has('payroll.print') ? route('payroll.print', $p->id) : url('/payroll/print/' . $p->id) }}" 
                                           target="_blank" 
                                           class="p-1.5 rounded-lg text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 inline-block transition-colors" 
                                           title="Print Payslip">
                                            <i class="fa-solid fa-print"></i>
                                        </a>

                                        <form action="{{ Route::has('payroll.destroy') ? route('payroll.destroy', $p->id) : url('/payroll/' . $p->id) }}" 
                                              method="POST" onsubmit="return confirm('Delete this payroll record?');" class="inline-block">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors" title="Delete">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                        <i class="fa-solid fa-file-invoice text-3xl text-slate-300 dark:text-slate-600 mb-2 block"></i>
                                        <span class="font-bold text-sm block text-slate-700 dark:text-slate-300">No Payroll Records Found</span>
                                        <span class="text-xs text-slate-400">Select a staff member on the left to generate their first payslip.</span>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($payslips->hasPages())
                        <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700/60">
                            {{ $payslips->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>

    </div>
@endsection