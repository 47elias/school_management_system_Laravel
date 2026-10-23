@extends('layouts.admin.app')

@section('title', 'Payment Proof Approvals | ' . env('SCHOOL_ACRONYM', 'SMS'))

@section('header')
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">School Fees Proofs</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Review payment documents submitted during student portal activation.</p>
    </div>
@endsection

@section('content')
    <div class="space-y-5">
        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800 dark:border-rose-800 dark:bg-rose-950/40 dark:text-rose-200">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">{{ $errors->first() }}</div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-700">
                <h2 class="font-bold text-slate-800 dark:text-slate-100">Submissions ({{ $applications->total() }})</h2>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse($applications as $application)
                    @php($student = $studentsByNationalId->get($application->identity_number))
                    <article class="grid gap-5 p-5 lg:grid-cols-[1fr_auto]">
                        <div class="space-y-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="font-bold text-slate-900 dark:text-white">{{ $application->student_name }}</h3>
                                <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase {{ $application->activation_payment_status === 'approved' ? 'bg-emerald-100 text-emerald-700' : ($application->activation_payment_status === 'rejected' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700') }}">
                                    {{ $application->activation_payment_status ?? 'pending' }}
                                </span>
                            </div>
                            <dl class="grid gap-2 text-xs text-slate-600 dark:text-slate-300 sm:grid-cols-4">
                                <div><dt class="text-slate-400">Tracking ID</dt><dd class="font-semibold">{{ $application->tracking_id }}</dd></div>
                                <div><dt class="text-slate-400">National ID</dt><dd class="font-semibold">{{ $application->identity_number }}</dd></div>
                                <div><dt class="text-slate-400">Gender</dt><dd class="font-semibold">{{ $application->gender ?? 'Not recorded' }}</dd></div>
                                <div><dt class="text-slate-400">Applied grade</dt><dd class="font-semibold">{{ $application->applied_grade }}</dd></div>
                            </dl>
                            @if($application->activation_payment_remarks)
                                <p class="text-xs text-slate-500">Previous note: {{ $application->activation_payment_remarks }}</p>
                            @endif
                            @if($student)
                                <p class="text-xs font-semibold text-slate-600 dark:text-slate-300">Existing student record: {{ $student->student_number }} · {{ $student->schoolClass?->class_name ?? $student->grade }}</p>
                            @else
                                <p class="text-xs text-slate-500">Student record will be created after payment approval and stream placement.</p>
                            @endif
                            <a href="{{ route('admissions.payment-proofs.file', $application->id) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-lg border border-indigo-200 px-3 py-2 text-xs font-bold text-indigo-700 hover:bg-indigo-50 dark:border-indigo-800 dark:text-indigo-300">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i> View proof of payment
                            </a>
                        </div>

                        @if($application->activation_payment_status !== 'approved')
                            <form action="{{ route('admissions.payment-proofs.review', $application->id) }}" method="POST" class="w-full space-y-2 lg:w-72">
                                @csrf
                                <textarea name="remarks" rows="2" placeholder="Optional note for the record" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs dark:border-slate-600 dark:bg-slate-900 dark:text-white">{{ old('remarks') }}</textarea>
                                <div class="flex gap-2">
                                    <button name="decision" value="approved" class="flex-1 rounded-lg bg-emerald-600 px-3 py-2 text-xs font-bold text-white hover:bg-emerald-700">Approve</button>
                                    <button name="decision" value="rejected" class="flex-1 rounded-lg bg-rose-600 px-3 py-2 text-xs font-bold text-white hover:bg-rose-700">Reject</button>
                                </div>
                            </form>
                        @endif
                    </article>
                @empty
                    <p class="p-8 text-center text-sm text-slate-500">No payment proofs have been submitted yet.</p>
                @endforelse
            </div>
            @if($applications->hasPages())
                <div class="border-t border-slate-100 p-4 dark:border-slate-700">{{ $applications->links() }}</div>
            @endif
        </div>

        @if($legacyStudents->isNotEmpty())
            <div class="overflow-hidden rounded-2xl border border-amber-200 bg-white shadow-sm dark:border-amber-900 dark:bg-slate-800">
                <div class="border-b border-amber-100 bg-amber-50 px-5 py-4 dark:border-amber-900 dark:bg-amber-950/30">
                    <h2 class="font-bold text-amber-900 dark:text-amber-200">Existing student records with payment proofs</h2>
                    <p class="mt-1 text-xs text-amber-800 dark:text-amber-300">These records were created before approval was moved ahead of student enrollment.</p>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-700">
                    @foreach($legacyStudents as $student)
                        <article class="grid gap-4 p-5 lg:grid-cols-[1fr_auto]">
                            <div>
                                <h3 class="font-bold text-slate-900 dark:text-white">{{ $student->surname }}, {{ $student->name }} <span class="ml-2 text-xs font-semibold text-slate-500">{{ $student->student_number }}</span></h3>
                                <p class="mt-1 text-xs text-slate-600 dark:text-slate-300">{{ $student->schoolClass?->class_name ?? $student->grade }} · {{ ucfirst($student->activation_payment_status ?? 'pending') }}</p>
                                @if($student->activation_payment_remarks)<p class="mt-1 text-xs text-slate-500">Previous note: {{ $student->activation_payment_remarks }}</p>@endif
                                <a href="{{ route('admissions.payment-proofs.students.file', $student->id) }}" target="_blank" rel="noopener" class="mt-3 inline-flex rounded-lg border border-indigo-200 px-3 py-2 text-xs font-bold text-indigo-700 dark:border-indigo-800 dark:text-indigo-300">View proof of payment</a>
                            </div>
                            @if($student->activation_payment_status !== 'approved')
                                <form action="{{ route('admissions.payment-proofs.students.review', $student->id) }}" method="POST" class="w-full space-y-2 lg:w-72">
                                    @csrf
                                    <textarea name="remarks" rows="2" placeholder="Optional note" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs dark:border-slate-600 dark:bg-slate-900 dark:text-white"></textarea>
                                    <div class="flex gap-2">
                                        <button name="decision" value="approved" class="flex-1 rounded-lg bg-emerald-600 px-3 py-2 text-xs font-bold text-white">Approve</button>
                                        <button name="decision" value="rejected" class="flex-1 rounded-lg bg-rose-600 px-3 py-2 text-xs font-bold text-white">Reject</button>
                                    </div>
                                </form>
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection
