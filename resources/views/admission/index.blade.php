{{-- resources/views/admissions/manage.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Admission Management | ' . env('SCHOOL_ACRONYM', 'SMS'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Admissions Registry</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Review and process student applications</p>
        </div>
    </div>
@endsection

@section('content')
    <div x-data="{ activeModal: null }" class="space-y-6">

        {{-- 1. STAT CARDS --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            {{-- Total Apps --}}
            <div class="bg-gradient-to-br from-indigo-500 to-blue-600 rounded-2xl p-5 text-white shadow-sm relative overflow-hidden">
                <div class="relative z-10">
                    <span class="text-xs font-bold uppercase tracking-wider opacity-80 block">Total Apps</span>
                    <h3 class="text-3xl font-black mt-2 tracking-tight">{{ $admissions->total() }}</h3>
                </div>
                <i class="fa-solid fa-folder-open absolute right-4 bottom-2 text-6xl opacity-15"></i>
            </div>

            {{-- Pending Review --}}
            <div class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl p-5 text-white shadow-sm relative overflow-hidden">
                <div class="relative z-10">
                    <span class="text-xs font-bold uppercase tracking-wider opacity-80 block">Pending Review</span>
                    <h3 class="text-3xl font-black mt-2 tracking-tight">{{ $pendingCount ?? $admissions->where('status', 'pending')->count() }}</h3>
                </div>
                <i class="fa-solid fa-hourglass-half absolute right-4 bottom-2 text-6xl opacity-15"></i>
            </div>

            {{-- Approved --}}
            <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-5 text-white shadow-sm relative overflow-hidden">
                <div class="relative z-10">
                    <span class="text-xs font-bold uppercase tracking-wider opacity-80 block">Approved</span>
                    <h3 class="text-3xl font-black mt-2 tracking-tight">{{ $approvedCount ?? $admissions->where('status', 'approved')->count() }}</h3>
                </div>
                <i class="fa-solid fa-circle-check absolute right-4 bottom-2 text-6xl opacity-15"></i>
            </div>

            {{-- Declined --}}
            <div class="bg-gradient-to-br from-rose-500 to-pink-600 rounded-2xl p-5 text-white shadow-sm relative overflow-hidden">
                <div class="relative z-10">
                    <span class="text-xs font-bold uppercase tracking-wider opacity-80 block">Declined / Rejected</span>
                    <h3 class="text-3xl font-black mt-2 tracking-tight">{{ $declinedCount ?? $admissions->where('status', 'declined')->count() }}</h3>
                </div>
                <i class="fa-solid fa-circle-xmark absolute right-4 bottom-2 text-6xl opacity-15"></i>
            </div>

        </div>

        {{-- 2. SEARCH BAR --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 p-4 transition-colors">
            <form action="{{ Route::has('admissions.manage') ? route('admissions.manage') : url('/admissions/manage') }}" method="GET">
                <div class="relative rounded-xl flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search by Student Name, ID Number, or Tracking ID..."
                           class="w-full pl-10 pr-28 py-2.5 text-xs font-medium rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <button type="submit" class="absolute right-1 px-4 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-sm">
                        SEARCH
                    </button>
                </div>
            </form>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-200 flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-lg"></i>
                    <p class="text-xs font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error') || $errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs text-rose-800 dark:border-rose-800/50 dark:bg-rose-950/40 dark:text-rose-200">
                {{ session('error') ?? $errors->first() }}
            </div>
        @endif

        {{-- 3. DATA TABLE --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
            <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/80">
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                    <i class="fa-solid fa-list text-indigo-500 mr-2.5"></i> Applicant Registry
                </h3>
            </div>

            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700/60 text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/80">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tracking ID</th>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Student Details</th>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Gender</th>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Grade</th>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Contact</th>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3.5 text-right font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-100 dark:divide-slate-700/60">
                        @forelse($admissions as $app)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-mono text-xs font-bold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-700/60 px-2.5 py-1 rounded-md border border-slate-200 dark:border-slate-600">
                                    {{ $app->tracking_id }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <strong class="text-xs font-bold text-slate-800 dark:text-slate-100 block uppercase">{{ $app->student_name }}</strong>
                                <span class="text-[11px] font-mono text-slate-400 flex items-center mt-0.5">
                                    <i class="fa-solid fa-id-card mr-1"></i> {{ $app->identity_number }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-slate-700 dark:text-slate-300">{{ $app->gender ?? 'Not recorded' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/50">
                                    {{ $app->applied_grade }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <strong class="text-slate-700 dark:text-slate-300 block">{{ $app->guardian_name }}</strong>
                                <span class="text-[11px] font-mono text-slate-400 flex items-center mt-0.5">
                                    <i class="fa-solid fa-phone mr-1"></i> {{ $app->guardian_phone }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($app->status == 'approved')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-extrabold tracking-wider uppercase bg-emerald-500 text-white shadow-sm">
                                        APPROVED
                                    </span>
                                @elseif($app->status == 'declined' || $app->status == 'rejected')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-extrabold tracking-wider uppercase bg-rose-500 text-white shadow-sm">
                                        DECLINED
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-extrabold tracking-wider uppercase bg-amber-500 text-white shadow-sm">
                                        PENDING
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <button type="button" @click="activeModal = 'app-{{ $app->id }}'" 
                                        class="inline-flex items-center px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 text-xs font-bold border border-slate-200 dark:border-slate-600 transition-colors">
                                    <i class="fa-solid fa-magnifying-glass-plus mr-1.5"></i> REVIEW
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-inbox text-2xl text-slate-300 dark:text-slate-600 mb-2 block"></i>
                                No applications found matching your search.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="divide-y divide-slate-100 dark:divide-slate-700/60 md:hidden">
                @forelse($admissions as $app)
                    <article class="p-4 space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h4 class="font-bold text-sm text-slate-800 dark:text-slate-100">{{ $app->student_name }}</h4>
                                <p class="mt-1 text-[11px] font-mono text-slate-500 break-all">{{ $app->identity_number }}</p>
                            </div>
                            <span class="shrink-0 px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase {{ $app->status === 'approved' ? 'bg-emerald-100 text-emerald-700' : ($app->status === 'declined' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700') }}">{{ $app->status }}</span>
                        </div>
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div><span class="block text-[10px] uppercase text-slate-400">Applied grade</span><span class="font-semibold text-slate-700 dark:text-slate-300">{{ $app->applied_grade }}</span></div>
                            <div><span class="block text-[10px] uppercase text-slate-400">Gender</span><span class="font-semibold text-slate-700 dark:text-slate-300">{{ $app->gender ?? '—' }}</span></div>
                            <div><span class="block text-[10px] uppercase text-slate-400">Guardian</span><span class="font-semibold text-slate-700 dark:text-slate-300">{{ $app->guardian_name }}</span></div>
                            <div class="col-span-2"><span class="block text-[10px] uppercase text-slate-400">Contact</span><span class="font-medium text-slate-700 dark:text-slate-300">{{ $app->guardian_phone }}</span></div>
                        </div>
                        <button type="button" @click="activeModal = 'app-{{ $app->id }}'" class="w-full rounded-xl bg-indigo-600 px-4 py-2.5 text-xs font-bold text-white"><i class="fa-solid fa-magnifying-glass-plus mr-2"></i>Review application</button>
                    </article>
                @empty
                    <p class="p-8 text-center text-xs text-slate-400">No applications found matching your search.</p>
                @endforelse
            </div>

            @if($admissions->hasPages())
                <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700/60">
                    {!! $admissions->appends(request()->query())->links() !!}
                </div>
            @endif
        </div>

        {{-- 4. REVIEW MODALS --}}
        @foreach($admissions as $app)
            <div x-show="activeModal === 'app-{{ $app->id }}'" 
                 class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                <div class="flex min-h-full items-start sm:items-center justify-center px-2 py-3 sm:px-4 sm:py-6">
                    <div @click="activeModal = null" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

                    <div class="relative w-full max-w-5xl max-h-[calc(100dvh-1.5rem)] overflow-y-auto p-4 sm:p-6 text-left bg-white dark:bg-slate-800 rounded-2xl sm:rounded-3xl shadow-2xl z-10 border border-slate-200 dark:border-slate-700">

                            <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-700">
                                <div>
                                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 flex items-center">
                                        <i class="fa-solid fa-pen-to-square text-indigo-500 mr-2"></i> Application Review
                                    </h3>
                                    <p class="text-xs text-slate-400 mt-0.5">
                                        Tracking ID: <strong class="text-indigo-600 dark:text-indigo-400 font-mono">{{ $app->tracking_id }}</strong> | Submitted: {{ $app->created_at->format('d M Y, h:i A') }}
                                    </p>
                                </div>
                                <button type="button" @click="activeModal = null" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>

                            <div class="py-5 sm:py-6 grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-8">
                                
                                {{-- LEFT: ALL DETAILS --}}
                                <div class="lg:col-span-7 space-y-6 text-xs">
                                    
                                    {{-- Student Personal Info --}}
                                    <div>
                                        <h4 class="font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 mb-3 pb-1 border-b border-slate-200 dark:border-slate-700/60 flex items-center">
                                            <i class="fa-solid fa-user mr-1.5"></i> 1. Student Personal Information
                                        </h4>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <div>
                                                <span class="block text-[10px] font-bold uppercase text-slate-400">Full Name</span>
                                                <span class="font-bold text-slate-800 dark:text-slate-100 uppercase">{{ $app->student_name }}</span>
                                            </div>
                                            <div>
                                                <span class="block text-[10px] font-bold uppercase text-slate-400">National ID / Birth Cert</span>
                                                <span class="font-mono font-bold text-slate-800 dark:text-slate-100">{{ $app->identity_number }}</span>
                                            </div>
                                            <div>
                                                <span class="block text-[10px] font-bold uppercase text-slate-400">Applied Grade</span>
                                                <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $app->applied_grade }}</span>
                                            </div>
                                            <div>
                                                <span class="block text-[10px] font-bold uppercase text-slate-400">Gender</span>
                                                <span class="font-semibold text-slate-800 dark:text-slate-100">{{ $app->gender ?? 'Not recorded' }}</span>
                                            </div>
                                            <div>
                                                <span class="block text-[10px] font-bold uppercase text-slate-400">Date of Birth</span>
                                                <span class="font-semibold text-slate-800 dark:text-slate-100">
                                                    {{ \Carbon\Carbon::parse($app->date_of_birth)->format('d F Y') }} ({{ \Carbon\Carbon::parse($app->date_of_birth)->age }} yrs)
                                                </span>
                                            </div>
                                            <div class="sm:col-span-2">
                                                <span class="block text-[10px] font-bold uppercase text-slate-400">Residential Address</span>
                                                <span class="font-semibold text-slate-800 dark:text-slate-100">{{ $app->address ?? 'Not Provided' }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Guardian Info --}}
                                    <div>
                                        <h4 class="font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 mb-3 pb-1 border-b border-slate-200 dark:border-slate-700/60 flex items-center">
                                            <i class="fa-solid fa-users mr-1.5"></i> 2. Guardian Information
                                        </h4>
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                            <div>
                                                <span class="block text-[10px] font-bold uppercase text-slate-400">Guardian Name</span>
                                                <span class="font-semibold text-slate-800 dark:text-slate-100">{{ $app->guardian_name }}</span>
                                            </div>
                                            <div>
                                                <span class="block text-[10px] font-bold uppercase text-slate-400">Phone Number</span>
                                                <span class="font-mono font-semibold text-slate-800 dark:text-slate-100">{{ $app->guardian_phone }}</span>
                                            </div>
                                            <div>
                                                <span class="block text-[10px] font-bold uppercase text-slate-400">Email Address</span>
                                                <span class="font-semibold text-slate-800 dark:text-slate-100">{{ $app->guardian_email ?? 'Not Provided' }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Academic History --}}
                                    <div>
                                        <h4 class="font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 mb-3 pb-1 border-b border-slate-200 dark:border-slate-700/60 flex items-center">
                                            <i class="fa-solid fa-graduation-cap mr-1.5"></i> 3. Academic & Disciplinary History
                                        </h4>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-2">
                                            <div>
                                                <span class="block text-[10px] font-bold uppercase text-slate-400">Previous School Attended</span>
                                                <span class="font-semibold text-slate-800 dark:text-slate-100">{{ $app->previous_school ?? 'Not Provided' }}</span>
                                            </div>
                                            <div>
                                                <span class="block text-[10px] font-bold uppercase text-slate-400">Subjects Passed / Results</span>
                                                <span class="font-semibold text-slate-800 dark:text-slate-100">{{ $app->subjects_passed ?? 'Not Provided' }}</span>
                                            </div>
                                        </div>
                                        @if($app->academic_history)
                                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700/60 text-slate-700 dark:text-slate-300 leading-relaxed">
                                                {{ $app->academic_history }}
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Attachments --}}
                                    <div>
                                        <h4 class="font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 mb-3 pb-1 border-b border-slate-200 dark:border-slate-700/60 flex items-center">
                                            <i class="fa-solid fa-paperclip mr-1.5"></i> 4. Uploaded Documents
                                        </h4>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <div>
                                                <span class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Results / Report Book</span>
                                                @if($app->results_file)
                                                    <a href="{{ asset($app->results_file) }}" target="_blank" 
                                                       class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 hover:border-indigo-500 font-bold text-indigo-600 dark:text-indigo-400 flex items-center transition-colors">
                                                        <i class="fa-solid fa-file-pdf text-rose-500 text-lg mr-2"></i> View Results Document
                                                    </a>
                                                @else
                                                    <span class="text-slate-400 italic">No Results Uploaded</span>
                                                @endif
                                            </div>
                                            <div>
                                                <span class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Recommendation Letter</span>
                                                @if($app->recommendation_letter)
                                                    <a href="{{ asset($app->recommendation_letter) }}" target="_blank" 
                                                       class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 hover:border-indigo-500 font-bold text-indigo-600 dark:text-indigo-400 flex items-center transition-colors">
                                                        <i class="fa-solid fa-file-lines text-indigo-500 text-lg mr-2"></i> View Recommendation Letter
                                                    </a>
                                                @else
                                                    <span class="text-slate-400 italic">No Recommendation Provided</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                {{-- RIGHT: DECISION FORM --}}
                                <div class="lg:col-span-5 bg-slate-50 dark:bg-slate-900/50 p-5 rounded-2xl border border-slate-200 dark:border-slate-700/60 space-y-4">
                                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center">
                                        <i class="fa-solid fa-gavel text-amber-500 mr-2"></i> Admissions Decision
                                    </h3>

                                    <form action="{{ route('admissions.update', $app->id) }}" method="POST" class="space-y-4">
                                        @csrf @method('PUT')
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Update Application Status</label>
                                        <select name="status" class="w-full px-3.5 py-2.5 text-xs font-bold rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                            <option value="pending" {{ $app->status == 'pending' ? 'selected' : '' }}>⏳ Pending Review</option>
                                            <option value="approved" {{ $app->status == 'approved' ? 'selected' : '' }}>✅ Approve Admission</option>
                                            <option value="declined" {{ $app->status == 'declined' ? 'selected' : '' }}>❌ Decline / Reject Application</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Admin Remarks / Feedback</label>
                                        <p class="text-[11px] text-slate-400 mb-2">Visible to parent on tracking portal and official letter.</p>
                                        <textarea name="admin_remarks" rows="5" placeholder="Enter reason for decision or instructions for enrollment..."
                                                  class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 leading-relaxed">{{ $app->admin_remarks }}</textarea>
                                    </div>

                                    <button type="submit" class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-sm flex items-center justify-center">
                                        <i class="fa-solid fa-floppy-disk mr-2"></i> SAVE DECISION
                                    </button>
                                    </form>
                                    <div class="border-t border-slate-200 dark:border-slate-700 pt-4">
                                        <h4 class="text-sm font-bold text-slate-800 dark:text-slate-100"><i class="fa-solid fa-user-check text-emerald-500 mr-2"></i>Enroll into School</h4>
                                        @if($enrolledStudents->has($app->identity_number))
                                            @php($enrolledStudent = $enrolledStudents->get($app->identity_number))
                                            <p class="mt-2 text-xs text-emerald-700 dark:text-emerald-300">Already enrolled in {{ $enrolledStudent->grade }} · Student ID {{ $enrolledStudent->student_number }}</p>
                                            <a href="{{ route('students.show', $enrolledStudent->id) }}" class="mt-3 inline-flex rounded-lg bg-emerald-100 px-3 py-2 text-xs font-bold text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300">Open student record</a>
                                        @elseif($app->status === 'approved')
                                            @if($app->activation_payment_status === 'approved')
                                            <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Applicant details will be copied into the student record. The applied grade stays fixed; the system selects a stream with available space.</p>
                                            <form action="{{ route('admissions.enroll', $app->id) }}" method="POST" class="mt-3 space-y-3">
                                                @csrf
                                                <div><label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-slate-500">Gender</label><select name="gender" required class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white"><option value="">Select gender</option><option value="Female" {{ $app->gender === 'Female' ? 'selected' : '' }}>Female</option><option value="Male" {{ $app->gender === 'Male' ? 'selected' : '' }}>Male</option></select></div>
                                                <div class="rounded-xl border border-indigo-100 bg-indigo-50 px-3 py-2.5 text-xs dark:border-indigo-900/60 dark:bg-indigo-950/30"><span class="block text-[10px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-300">Approved grade</span><span class="mt-1 block font-bold text-slate-800 dark:text-slate-100">{{ $app->applied_grade }}</span></div>
                                                <button type="submit" class="w-full rounded-xl bg-emerald-600 px-4 py-3 text-xs font-bold text-white hover:bg-emerald-700"><i class="fa-solid fa-user-plus mr-2"></i>Add student to school</button>
                                            </form>
                                            @else
                                                <p class="mt-2 text-xs text-amber-700 dark:text-amber-300">Awaiting approved proof of payment. The student record will be created after payment approval.</p>
                                            @endif
                                        @else
                                            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Approve this application to enable direct enrollment.</p>
                                        @endif
                                    </div>
                                </div>

                            </div>
                    </div>
                </div>
            </div>
        @endforeach

    </div>
@endsection
