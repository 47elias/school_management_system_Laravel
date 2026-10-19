@extends('layouts.admin.app')

@section('title', 'Teacher Dashboard')

@section('content_header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-2 border-b border-gray-200">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Teacher Dashboard</h1>
            <p class="text-sm text-gray-600">Welcome back, {{ auth()->user()->name }}</p>
        </div>
        <div class="mt-2 sm:mt-0">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                <i class="fas fa-chalkboard-teacher mr-1.5"></i> Academic Term Active
            </span>
        </div>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Stat Cards Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Assigned Subjects -->
        <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-gray-500">My Subjects</p>
                <h3 class="text-2xl font-black text-gray-900 mt-1">{{ $assignedSubjectsCount ?? 0 }}</h3>
                <a href="#" class="text-xs font-bold text-blue-600 hover:underline mt-2 inline-block">View Allocations &rarr;</a>
            </div>
            <div class="w-12 h-12 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="fas fa-book"></i>
            </div>
        </div>

        <!-- Total Students -->
        <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-gray-500">Total Students</p>
                <h3 class="text-2xl font-black text-gray-900 mt-1">{{ $totalStudentsCount ?? 0 }}</h3>
                    <a href="#" class="text-xs font-bold text-blue-600 hover:underline mt-2 inline-block">View Student List &rarr;</a>
                </div>
            <div class="w-12 h-12 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fas fa-user-graduate"></i>
            </div>
        </div>

        <!-- Class Activities -->
        <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-gray-500">Active Tasks</p>
                <h3 class="text-2xl font-black text-gray-900 mt-1">{{ $activitiesCount ?? 0 }}</h3>
                <a href="#" class="text-xs font-bold text-blue-600 hover:underline mt-2 inline-block">Record Activity Marks &rarr;</a>
            </div>
            <div class="w-12 h-12 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                <i class="fas fa-tasks"></i>
            </div>
        </div>

        <!-- Pending Marks Entry -->
        <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-gray-500">Exams Recorded</p>
                <h3 class="text-2xl font-black text-gray-900 mt-1">{{ $examsRecordedCount ?? 0 }}</h3>
                <a href="#" class="text-xs font-bold text-blue-600 hover:underline mt-2 inline-block">Manage Exam Marks &rarr;</a>
            </div>
            <div class="w-12 h-12 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-xl">
                <i class="fas fa-edit"></i>
            </div>
        </div>
    </div>

    <!-- Quick Action Shortcuts & Overview -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Panel: Assigned Classes & Subjects -->
        <div class="lg:col-span-2 bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between bg-gray-50">
                <h3 class="font-bold text-sm text-gray-900 flex items-center space-x-2">
                    <i class="fas fa-chalkboard text-blue-600"></i>
                    <span>Assigned Subjects & Classes</span>
                </h3>
                <a href="#" class="text-xs font-semibold text-blue-600 hover:underline">View All</a>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($assignedSubjects ?? [] as $assignment)
                    <div class="p-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <div>
                            <h4 class="font-bold text-sm text-gray-900">{{ $assignment->subject->name ?? 'Subject' }}</h4>
                            <p class="text-xs text-gray-500">Class: {{ $assignment->schoolClass->name ?? 'Class' }} | Code: {{ $assignment->subject->code ?? 'N/A' }}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <a href="#" class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 text-xs font-semibold">
                                Activities
                            </a>
                            <a href="#" class="px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-xs font-semibold">
                                Marks
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500">
                        <i class="fas fa-info-circle text-2xl text-gray-400 mb-2 block"></i>
                        <p class="text-sm">No subjects allocated for the active term yet.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right Side Panel: Quick Actions -->
        <div class="space-y-4">
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-5">
                <h3 class="font-bold text-sm text-gray-900 mb-3 border-b border-gray-100 pb-2 flex items-center space-x-2">
                    <i class="fas fa-bolt text-amber-500"></i>
                    <span>Quick Tasks</span>
                </h3>
                <div class="space-y-2">
                    <a href="#" class="flex items-center justify-between p-3 rounded-lg border border-gray-200 hover:border-blue-500 hover:bg-blue-50/50 transition-all text-xs font-semibold text-gray-800">
                        <span class="flex items-center space-x-2">
                            <i class="fas fa-plus-circle text-blue-600"></i>
                            <span>Record Activity Marks</span>
                        </span>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </a>

                    <a href="#" class="flex items-center justify-between p-3 rounded-lg border border-gray-200 hover:border-blue-500 hover:bg-blue-50/50 transition-all text-xs font-semibold text-gray-800">
                        <span class="flex items-center space-x-2">
                            <i class="fas fa-pen-nib text-emerald-600"></i>
                            <span>Enter Exam Scores</span>
                        </span>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </a>

                    <a href="#" class="flex items-center justify-between p-3 rounded-lg border border-gray-200 hover:border-blue-500 hover:bg-blue-50/50 transition-all text-xs font-semibold text-gray-800">
                        <span class="flex items-center space-x-2">
                            <i class="fas fa-clipboard-list text-purple-600"></i>
                            <span>Class Roster & Profiles</span>
                        </span>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection