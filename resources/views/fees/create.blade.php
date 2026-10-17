{{-- resources/views/fees/create.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Collect Fees | ' . env('SCHOOL_ACRONYM', 'SMS'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Fee Collection</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Record a direct manual payment or dispatch a remote digital payment link</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ Route::has('fees.index') ? route('fees.index') : url('/fees') }}" 
               class="inline-flex items-center px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all border border-slate-200 dark:border-slate-700 shadow-sm">
                <i class="fa-solid fa-arrow-left mr-2"></i> Payment History
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div x-data="{ activeTab: 'direct', directMethod: 'Cash', onlineChannel: 'paynow_link' }" class="max-w-4xl mx-auto space-y-6">

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-200 flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-lg"></i>
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider">Success</h4>
                        <p class="text-xs font-medium mt-0.5">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('online_success'))
            <div class="p-4 rounded-xl bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-800/50 text-sky-800 dark:text-sky-200 flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-paper-plane text-sky-600 dark:text-sky-400 text-lg"></i>
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider">Link Sent / Processing</h4>
                        <p class="text-xs font-medium mt-0.5">{{ session('online_success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Tab Navigation Card --}}
        <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
            <div class="flex border-b border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50 p-2">
                <button type="button" @click="activeTab = 'direct'" 
                        :class="activeTab === 'direct' ? 'bg-white dark:bg-slate-800 text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'"
                        class="flex-1 py-3 px-4 rounded-2xl text-xs font-bold transition-all flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-handshake"></i>
                    <span>Record Direct Payment</span>
                </button>
                <button type="button" @click="activeTab = 'online'" 
                        :class="activeTab === 'online' ? 'bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'"
                        class="flex-1 py-3 px-4 rounded-2xl text-xs font-bold transition-all flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-globe"></i>
                    <span>Send Online Link</span>
                </button>
            </div>

            <div class="p-6">
                {{-- TAB 1: DIRECT PAYMENT ENTRY --}}
                <div x-show="activeTab === 'direct'">
                    <form action="{{ Route::has('fees.store') ? route('fees.store') : url('/fees/store') }}" method="POST" class="space-y-5">
                        @csrf

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">Search Student <span class="text-rose-500">*</span></label>
                            <select name="student_id" required 
                                    class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-semibold">
                                <option value="">-- Select Student --</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                                        {{ $student->student_number }} - {{ $student->surname }}, {{ $student->name }} ({{ $student->schoolClass->class_name ?? $student->grade }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">Academic Term <span class="text-rose-500">*</span></label>
                                <select name="term_id" required class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-semibold">
                                    @foreach($terms as $term)
                                        <option value="{{ $term->id }}" {{ $term->is_current ? 'selected' : '' }}>
                                            {{ $term->term_name }} ({{ $term->academic_year }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">Date of Payment <span class="text-rose-500">*</span></label>
                                <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-semibold">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">Amount Paid ($) <span class="text-rose-500">*</span></label>
                            <input type="number" name="amount_paid" step="0.01" placeholder="0.00" required
                                   class="w-full px-3.5 py-2.5 text-sm font-bold rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>

                        {{-- Direct Method Selection Grid --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">Payment Method</label>
                            <input type="hidden" name="payment_method" :value="directMethod">
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                <template x-for="m in [
                                    {id: 'Cash', label: 'Physical Cash', icon: 'fa-money-bill-1'},
                                    {id: 'EcoCash', label: 'EcoCash', icon: 'fa-mobile-screen-button'},
                                    {id: 'ZIPIT', label: 'ZIPIT / Bank', icon: 'fa-money-bill-transfer'},
                                    {id: 'Card', label: 'Master/Visa Card', icon: 'fa-credit-card'}
                                ]">
                                    <button type="button" @click="directMethod = m.id" 
                                            :class="directMethod === m.id ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 ring-2 ring-emerald-500' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400'"
                                            class="p-4 rounded-2xl border text-center transition-all">
                                        <i class="fa-solid text-xl mb-1" :class="m.icon"></i>
                                        <span class="block text-xs font-bold" x-text="m.label"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">Reference Number</label>
                            <input type="text" name="reference_no" placeholder="e.g. TXN-123456"
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">Remarks / Notes</label>
                            <textarea name="remarks" rows="2" placeholder="Specific details regarding this transaction..."
                                      class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                        </div>

                        <div class="pt-3 border-t border-slate-100 dark:border-slate-700/60 flex justify-end">
                            <button type="submit" class="inline-flex items-center px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-sm">
                                <i class="fa-solid fa-floppy-disk mr-2"></i> Save Payment Record
                            </button>
                        </div>
                    </form>
                </div>

                {{-- TAB 2: REMOTE ONLINE LINKS --}}
                <div x-show="activeTab === 'online'" style="display: none;">
                    <form action="{{ Route::has('fees.payOnline') ? route('fees.payOnline') : url('/fees/pay-online') }}" method="POST" class="space-y-5">
                        @csrf

                        <div class="p-3.5 rounded-xl bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-800/50 text-sky-800 dark:text-sky-300 text-xs flex items-center space-x-3">
                            <i class="fa-solid fa-circle-info text-sky-500 text-lg"></i>
                            <p>Generates a remote payment link sent directly to parents via <strong>EcoCash, OneMoney, or Card</strong>.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">Search Student <span class="text-rose-500">*</span></label>
                            <select name="student_id" required 
                                    class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-semibold">
                                <option value="">-- Select Student --</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}" data-phone="{{ $student->phone ?? '' }}" data-email="{{ $student->email ?? '' }}">
                                        {{ $student->student_number }} - {{ $student->surname }}, {{ $student->name }} ({{ $student->schoolClass->class_name ?? $student->grade }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">Academic Term <span class="text-rose-500">*</span></label>
                                <select name="term_id" required class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-semibold">
                                    @foreach($terms as $term)
                                        <option value="{{ $term->id }}" {{ $term->is_current ? 'selected' : '' }}>
                                            {{ $term->term_name }} ({{ $term->academic_year }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">Amount Due ($) <span class="text-rose-500">*</span></label>
                                <input type="number" name="amount_paid" step="0.01" placeholder="0.00" required
                                       class="w-full px-3.5 py-2.5 text-sm font-bold rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        {{-- Online Channel Selector --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">Remote Payment Channel</label>
                            <input type="hidden" name="online_channel" :value="onlineChannel">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <button type="button" @click="onlineChannel = 'paynow_link'" 
                                        :class="onlineChannel === 'paynow_link' ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 ring-2 ring-indigo-500' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400'"
                                        class="p-4 rounded-2xl border text-center transition-all">
                                    <i class="fa-solid fa-envelope text-xl mb-1"></i>
                                    <span class="block text-xs font-bold">Email Payment Link</span>
                                </button>
                                <button type="button" @click="onlineChannel = 'ecocash_push'" 
                                        :class="onlineChannel === 'ecocash_push' ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 ring-2 ring-indigo-500' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400'"
                                        class="p-4 rounded-2xl border text-center transition-all">
                                    <i class="fa-solid fa-mobile-retro text-xl mb-1"></i>
                                    <span class="block text-xs font-bold">EcoCash USSD Push</span>
                                </button>
                                <button type="button" @click="onlineChannel = 'card'" 
                                        :class="onlineChannel === 'card' ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 ring-2 ring-indigo-500' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400'"
                                        class="p-4 rounded-2xl border text-center transition-all">
                                    <i class="fa-solid fa-credit-card text-xl mb-1"></i>
                                    <span class="block text-xs font-bold">Remote Card Payment</span>
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Parent Mobile Number</label>
                                <input type="text" name="payer_phone" placeholder="e.g. 077XXXXXXX" :required="onlineChannel === 'ecocash_push'"
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <span class="text-[10px] text-slate-400 mt-1 block">Required for direct USSD push alerts.</span>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Parent Email</label>
                                <input type="email" name="payer_email" placeholder="parent@example.com"
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <span class="text-[10px] text-slate-400 mt-1 block">Receives receipt & payment link.</span>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-100 dark:border-slate-700/60 flex justify-end">
                            <button type="submit" class="inline-flex items-center px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-sm">
                                <i class="fa-solid fa-paper-plane mr-2"></i> Send Remote Payment Link
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection