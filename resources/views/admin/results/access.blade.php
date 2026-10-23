@extends('layouts.admin.app')

@section('title', 'Result Publishing & Fee Exemptions')

@section('header')
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Result Publishing & Fee Exemptions</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Release term results and record approved exceptions to outstanding school fees.</p>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">{{ $errors->first() }}</div>
        @endif

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                <div>
                    <h2 class="font-bold text-slate-800 dark:text-slate-100">Publish results by term</h2>
                    @if($publication?->is_published)
                        <p class="mt-1 text-xs text-emerald-700 dark:text-emerald-300">Published {{ $publication->published_at?->format('d M Y H:i') }} by {{ $publication->publishedBy?->name ?? 'an administrator' }}</p>
                    @else
                        <p class="mt-1 text-xs text-amber-700 dark:text-amber-300">Unpublished. Students cannot see this term’s results.</p>
                    @endif
                </div>
                <form method="GET" action="{{ route('results.access') }}" class="flex items-center gap-2">
                    <label for="term_id" class="text-xs font-semibold text-slate-500">Term</label>
                    <select id="term_id" name="term_id" onchange="this.form.submit()" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                        @foreach($terms as $item)
                            <option value="{{ $item->id }}" @selected($term?->id === $item->id)>{{ $item->term_name }} ({{ $item->academic_year }})</option>
                        @endforeach
                    </select>
                </form>
            </div>
            @if($term)
                <form method="POST" action="{{ route('results.publish') }}" class="mt-4">
                    @csrf
                    <input type="hidden" name="term_id" value="{{ $term->id }}">
                    <input type="hidden" name="is_published" value="{{ $publication?->is_published ? 0 : 1 }}">
                    <button type="submit" class="rounded-lg px-4 py-2 text-sm font-bold text-white {{ $publication?->is_published ? 'bg-amber-600 hover:bg-amber-700' : 'bg-emerald-600 hover:bg-emerald-700' }}">
                        {{ $publication?->is_published ? 'Unpublish term results' : 'Publish all term results' }}
                    </button>
                    <span class="ml-2 text-xs text-slate-500">Publishing releases all marks entered for this term.</span>
                </form>
            @else
                <p class="mt-4 text-sm text-slate-500">Create an academic term before publishing results.</p>
            @endif
        </section>

        @if($term)
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <div class="flex flex-col justify-between gap-3 border-b border-slate-200 p-5 dark:border-slate-700 sm:flex-row sm:items-center">
                    <div>
                        <h2 class="font-bold text-slate-800 dark:text-slate-100">Student fee exceptions · {{ $term->term_name }}</h2>
                        <p class="mt-1 text-xs text-slate-500">Students with an outstanding balance cannot view results unless an exemption is active.</p>
                    </div>
                    <form method="GET" action="{{ route('results.access') }}" class="flex gap-2">
                        <input type="hidden" name="term_id" value="{{ $term->id }}">
                        <input type="search" name="search" value="{{ request('search') }}" placeholder="Search name or student number" class="rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                        <button class="rounded-lg bg-slate-800 px-3 py-2 text-sm font-semibold text-white">Search</button>
                    </form>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500 dark:bg-slate-900/50">
                            <tr><th class="px-5 py-3">Student</th><th class="px-5 py-3">Stream</th><th class="px-5 py-3">Fee balance</th><th class="px-5 py-3">Exemption</th><th class="px-5 py-3">Manage</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @forelse($studentRows as $row)
                                @php($student = $row['student'])
                                <tr>
                                    <td class="px-5 py-4"><strong class="block text-slate-800 dark:text-slate-100">{{ $student->surname }}, {{ $student->name }}</strong><span class="text-xs text-slate-500">{{ $student->student_number }}</span></td>
                                    <td class="px-5 py-4 text-slate-600 dark:text-slate-300">{{ $student->schoolClass?->class_name ?? $student->grade ?? '—' }}</td>
                                    <td class="px-5 py-4 font-mono {{ $row['balance'] > 0.004 ? 'font-bold text-rose-600' : 'text-emerald-600' }}">${{ number_format(max(0, $row['balance']), 2) }}</td>
                                    <td class="px-5 py-4">
                                        @if($row['exemption'])
                                            <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-800">Active</span>
                                            <p class="mt-1 max-w-sm whitespace-normal text-xs text-slate-500">{{ $row['exemption']->reason }}</p>
                                        @else
                                            <span class="text-xs text-slate-400">None</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        @if($row['exemption'])
                                            <form method="POST" action="{{ route('results.fee_exemptions.revoke', $row['exemption']) }}" onsubmit="return confirm('Revoke this result fee exemption? Its audit record will remain.');">
                                                @csrf
                                                <button class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-bold text-rose-700 hover:bg-rose-50">Revoke</button>
                                            </form>
                                        @else
                                            <details>
                                                <summary class="cursor-pointer rounded-lg bg-indigo-600 px-3 py-1.5 text-center text-xs font-bold text-white">Grant exemption</summary>
                                                <form method="POST" action="{{ route('results.fee_exemptions.grant') }}" class="mt-3 min-w-64 space-y-2">
                                                    @csrf
                                                    <input type="hidden" name="student_id" value="{{ $student->id }}">
                                                    <input type="hidden" name="term_id" value="{{ $term->id }}">
                                                    <textarea name="reason" required minlength="5" maxlength="2000" rows="3" placeholder="Reason for exemption (required)" class="w-full rounded-lg border border-slate-300 p-2 text-xs dark:border-slate-600 dark:bg-slate-900"></textarea>
                                                    <button class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white">Save exemption</button>
                                                </form>
                                            </details>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">No students match this search.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4">{{ $students->links() }}</div>
            </section>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <div class="border-b border-slate-200 p-5 dark:border-slate-700">
                    <h2 class="font-bold text-slate-800 dark:text-slate-100">Exemption audit log</h2>
                    <p class="mt-1 text-xs text-slate-500">Reasons, granting administrators, and revocations are retained here.</p>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($exemptions as $entry)
                        <div class="flex flex-col justify-between gap-2 px-5 py-4 text-sm sm:flex-row">
                            <div><strong>{{ $entry->student?->student_number }} · {{ $entry->student?->name }} {{ $entry->student?->surname }}</strong><p class="mt-1 text-slate-600 dark:text-slate-300">{{ $entry->reason }}</p></div>
                            <div class="shrink-0 text-xs text-slate-500">Granted by {{ $entry->grantedBy?->name ?? 'Unknown' }} · {{ $entry->created_at?->format('d M Y H:i') }}<br>{{ $entry->revoked_at ? 'Revoked by ' . ($entry->revokedBy?->name ?? 'Unknown') . ' · ' . $entry->revoked_at->format('d M Y H:i') : 'Active' }}</div>
                        </div>
                    @empty
                        <p class="p-5 text-sm text-slate-500">No fee exemptions have been recorded for this term.</p>
                    @endforelse
                </div>
                <div class="p-4">{{ $exemptions->links() }}</div>
            </section>
        @endif
    </div>
@endsection
