@extends('layouts.admin.app')

@section('title', 'Audit Records | ' . env('SCHOOL_ACRONYM', 'SMS'))

@section('header')
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Audit Records</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">A record of changes made to school information.</p>
    </div>
@endsection

@section('content')
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="flex flex-col gap-4 border-b border-slate-200 px-5 py-4 dark:border-slate-700 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">Change history</h2>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Showing {{ number_format($logs->firstItem() ?? 0) }}-{{ number_format($logs->lastItem() ?? 0) }} of {{ number_format($logs->total()) }} records</p>
            </div>
            <form method="GET" action="{{ route('audit.logs') }}" class="grid grid-cols-1 gap-2 sm:grid-cols-[minmax(220px,1fr)_150px_auto_auto]">
                <label class="sr-only" for="audit-search">Search audit records</label>
                <input id="audit-search" type="search" name="search" value="{{ $search }}" placeholder="Search name, record or ID" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:focus:ring-indigo-950">
                <label class="sr-only" for="audit-event">Filter by action</label>
                <select id="audit-event" name="event" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-slate-600 dark:bg-slate-900 dark:text-white">
                    <option value="">All actions</option>
                    @foreach(['created' => 'Created', 'updated' => 'Updated', 'deleted' => 'Deleted', 'restored' => 'Restored'] as $value => $label)
                        <option value="{{ $value }}" @selected($event === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Apply</button>
                @if($search !== '' || $event !== '')
                    <a href="{{ route('audit.logs') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-center text-sm font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">Clear</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm dark:divide-slate-700">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-900/40 dark:text-slate-400">
                    <tr>
                        <th scope="col" class="whitespace-nowrap px-5 py-3">Date and time</th>
                        <th scope="col" class="px-5 py-3">Staff member</th>
                        <th scope="col" class="px-5 py-3">Action</th>
                        <th scope="col" class="px-5 py-3">Record</th>
                        <th scope="col" class="px-5 py-3">Changes</th>
                        <th scope="col" class="px-5 py-3"><span class="sr-only">Details</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($logs as $log)
                        @php
                            $oldValues = $log->old_values ?? [];
                            $newValues = $log->new_values ?? [];
                            $changedFields = array_unique(array_merge(array_keys($oldValues), array_keys($newValues)));
                            $record = $log->auditable;
                            $recordName = null;
                            foreach (['student_name', 'exam_name', 'title', 'item_name', 'class_name', 'subject_name', 'fee_name', 'term_name', 'description', 'name', 'reference_no', 'student_number', 'employee_id', 'grade', 'subject', 'day'] as $field) {
                                $candidate = $record?->{$field} ?? $newValues[$field] ?? $oldValues[$field] ?? null;
                                if (is_scalar($candidate) && trim((string) $candidate) !== '') {
                                    $recordName = trim((string) $candidate);
                                    if ($field === 'name') {
                                        $surname = $record?->surname ?? $newValues['surname'] ?? $oldValues['surname'] ?? null;
                                        if ($surname) $recordName .= ' ' . $surname;
                                    }
                                    break;
                                }
                            }
                            $relatedNames = [];
                            foreach (['student', 'subject', 'schoolClass', 'item', 'user', 'teacher'] as $relation) {
                                if (!$record || !$record->relationLoaded($relation) || !$record->{$relation}) continue;
                                $related = $record->{$relation};
                                $relatedName = $related->name ?? $related->student_name ?? $related->subject_name ?? $related->class_name ?? $related->item_name ?? $related->title ?? null;
                                if ($relatedName) {
                                    if ($related->surname) $relatedName .= ' ' . $related->surname;
                                    $relatedNames[] = $relatedName;
                                }
                            }
                            if ($record && $record->relationLoaded('subjectAssignment') && $record->subjectAssignment) {
                                if ($record->subjectAssignment->subject) $relatedNames[] = $record->subjectAssignment->subject->subject_name;
                                if ($record->subjectAssignment->schoolClass) $relatedNames[] = $record->subjectAssignment->schoolClass->class_name;
                            }
                            $relatedNames = array_values(array_unique($relatedNames));
                            if (!$recordName && $relatedNames) $recordName = $relatedNames[0];
                            $actor = $log->user;
                            $actorName = $actor?->name ?? 'System';
                            if ($actor && $actor->surname) $actorName .= ' ' . $actor->surname;
                            $actionStyle = match ($log->event) {
                                'created' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300',
                                'updated' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300',
                                'deleted' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300',
                                'restored' => 'bg-violet-50 text-violet-700 dark:bg-violet-950/40 dark:text-violet-300',
                                default => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200',
                            };
                        @endphp
                        <tr class="align-top hover:bg-slate-50/70 dark:hover:bg-slate-700/20">
                            <td class="whitespace-nowrap px-5 py-4 text-xs text-slate-600 dark:text-slate-300">
                                <span class="block font-semibold text-slate-800 dark:text-slate-100">{{ $log->created_at?->format('d M Y') ?? 'Unknown date' }}</span>
                                <span class="mt-1 block text-slate-400">{{ $log->created_at?->format('H:i:s') ?? '' }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="block font-semibold text-slate-800 dark:text-slate-100">{{ $actorName }}</span>
                                <span class="mt-1 block text-xs text-slate-400">{{ $actor ? class_basename($log->user_type ?? get_class($actor)) : 'Automatic action' }}</span>
                            </td>
                            <td class="px-5 py-4"><span class="inline-block rounded-md px-2 py-1 text-xs font-semibold {{ $actionStyle }}">{{ ucfirst($log->event) }}</span></td>
                            <td class="px-5 py-4">
                                <span class="block font-semibold text-slate-800 dark:text-slate-100">{{ $recordName ?: class_basename($log->auditable_type ?? 'Record') }}</span>
                                <span class="mt-1 block text-xs text-slate-400">{{ class_basename($log->auditable_type ?? 'Record') }} - ID {{ $log->auditable_id ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-600 dark:text-slate-300">
                                {{ count($changedFields) }} {{ count($changedFields) === 1 ? 'field' : 'fields' }}
                                @if($log->ip_address)<span class="mt-1 block text-slate-400">IP {{ $log->ip_address }}</span>@endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <details class="inline-block text-left">
                                    <summary class="cursor-pointer list-none whitespace-nowrap rounded-md border border-slate-200 px-2.5 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">View changes</summary>
                                    <div class="mt-2 w-72 max-w-[75vw] rounded-lg border border-slate-200 bg-white p-3 text-left shadow-sm dark:border-slate-600 dark:bg-slate-800">
                                        <div class="mb-3 border-b border-slate-100 pb-3 dark:border-slate-700">
                                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Affected record</p>
                                            <p class="mt-1 break-words text-sm font-semibold text-slate-900 dark:text-white">{{ $recordName ?: 'Record details unavailable' }}</p>
                                            @if($relatedNames)<p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ implode(' / ', $relatedNames) }}</p>@endif
                                            <p class="mt-1 text-[11px] text-slate-400">{{ class_basename($log->auditable_type ?? 'Record') }} - ID {{ $log->auditable_id ?? '-' }}</p>
                                        </div>
                                        @if(count($changedFields))
                                            <div class="max-h-72 space-y-2 overflow-y-auto">
                                                @foreach($changedFields as $field)
                                                    @php
                                                        $oldValue = $oldValues[$field] ?? null;
                                                        $newValue = $newValues[$field] ?? null;
                                                        $displayValue = fn ($value) => is_scalar($value) || $value === null ? (string) ($value ?? '-') : json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                                                    @endphp
                                                    <div class="border-b border-slate-100 pb-2 last:border-0 last:pb-0 dark:border-slate-700">
                                                        <p class="font-semibold text-slate-800 dark:text-slate-100">{{ str($field)->replace('_', ' ')->title() }}</p>
                                                        <p class="mt-1 break-words text-slate-500 dark:text-slate-300"><span class="text-rose-600 dark:text-rose-300">{{ $displayValue($oldValue) }}</span><span class="mx-1.5 text-slate-400">-&gt;</span><span class="text-emerald-700 dark:text-emerald-300">{{ $displayValue($newValue) }}</span></p>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-xs text-slate-500 dark:text-slate-400">No field values were recorded.</p>
                                        @endif
                                        <div class="mt-3 border-t border-slate-100 pt-2 text-[11px] text-slate-500 dark:border-slate-700 dark:text-slate-400">
                                            <p>Changed by: <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $actorName }}</span></p>
                                            @if($log->created_at)<p class="mt-1">Date: {{ $log->created_at->format('d M Y, H:i:s') }}</p>@endif
                                            @if($log->ip_address)<p class="mt-1">IP address: {{ $log->ip_address }}</p>@endif
                                            @if($log->user_agent)<p class="mt-1 break-words">Device: {{ $log->user_agent }}</p>@endif
                                            @if($log->url)<p class="mt-1 break-all">Page: {{ $log->url }}</p>@endif
                                        </div>
                                        @if($log->tags)<p class="mt-2 border-t border-slate-100 pt-2 text-[11px] text-slate-400 dark:border-slate-700">{{ $log->tags }}</p>@endif
                                    </div>
                                </details>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-sm text-slate-500 dark:text-slate-400">No audit records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50 px-5 py-4 dark:border-slate-700 dark:bg-slate-800 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-slate-500 dark:text-slate-400">Page {{ $logs->currentPage() }} of {{ $logs->lastPage() }}</p>
                {{ $logs->links() }}
            </div>
        @endif
    </div>
@endsection
