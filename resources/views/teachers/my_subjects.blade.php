@extends('layouts.admin.app')

@section('title', 'My Assigned Subjects')

@section('content_header')
    <div class="flex items-center justify-between pb-2 border-b border-gray-200">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">My Assigned Subjects</h1>
            <p class="text-sm text-gray-600">Subjects and classes assigned to you for the current academic session</p>
        </div>
    </div>
@endsection

@section('content')
<div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
        <h3 class="font-bold text-sm text-gray-900">Allocated Classes & Course Modules</h3>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-gray-800">
            <thead class="bg-gray-100 uppercase font-bold text-gray-700 border-b border-gray-200">
                <tr>
                    <th class="py-3 px-4">Subject Name</th>
                    <th class="py-3 px-4">Subject Code</th>
                    <th class="py-3 px-4">Class / Stream</th>
                    <th class="py-3 px-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($assignments as $assignment)
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-4 font-bold text-gray-900">{{ $assignment->subject->name ?? 'N/A' }}</td>
                        <td class="py-3 px-4 font-mono">{{ $assignment->subject->code ?? 'N/A' }}</td>
                        <td class="py-3 px-4 font-semibold">{{ $assignment->schoolClass->name ?? 'N/A' }}</td>
                        <td class="py-3 px-4 text-center space-x-2">
                            <a href="#" class="px-3 py-1 rounded bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs">
                                Activities
                            </a>
                            <a href="#" class="px-3 py-1 rounded bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs">
                                Enter Marks
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-8 text-center text-gray-500 font-semibold">
                            No subjects currently assigned.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection