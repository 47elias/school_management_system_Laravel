{{-- resources/views/students/create.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Register Student | ' . env('SCHOOL_ACRONYM', 'SIT'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Student Registration</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Admission portal for registering new student records into class streams</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('students.index') }}" 
               class="inline-flex items-center px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 text-xs font-bold transition-all border border-slate-200 dark:border-slate-700">
                <i class="fa-solid fa-arrow-left mr-2"></i> Back to Student Directory
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/50 text-rose-800 dark:text-rose-200 shadow-sm">
                <div class="flex items-start space-x-3">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600 dark:text-rose-400 text-lg mt-0.5"></i>
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider">Submission Failed! Please check inputs:</h4>
                        <ul class="mt-1.5 space-y-1 list-disc list-inside text-xs font-medium">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        {{-- Registration Card --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
            
            <form method="POST" action="{{ route('students.store') }}" class="p-6 space-y-8">
                @csrf

                {{-- SECTION 1: Academic Placement & Stream Assignment --}}
                <div class="space-y-4">
                    <div class="flex items-center">
                        <span class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 mr-3 flex items-center">
                            <i class="fa-solid fa-graduation-cap text-sm mr-2"></i> 1. Academic Placement & Stream Assignment
                        </span>
                        <div class="flex-grow border-t border-slate-200 dark:border-slate-700"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="term_id" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                                Admission Term <span class="text-rose-500">*</span>
                            </label>
                            <select name="term_id" id="term_id" required
                                    class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-semibold">
                                <option value="">-- Select Admission Term --</option>
                                @foreach($terms as $term)
                                    <option value="{{ $term->id }}" @selected(old('term_id', $currentTerm?->id) == $term->id)>
                                        {{ $term->display_name }} ({{ $term->start_date->format('d M Y') }}) - {{ $term->is_current ? 'Current' : 'Future' }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Choose the current term or a configured future term. Future enrolments start on the term's start date without activating that term.</p>
                            @if($terms->isEmpty())
                                <p class="text-xs text-rose-600 mt-1">Configure a current or future term before registering students.</p>
                            @endif
                        </div>

                        <div>
                            <label for="class_id" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                                Sub-Class Stream <span class="text-rose-500">*</span>
                            </label>
                            <select name="class_id" id="class_id" required
                                    class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-semibold">
                                <option value="">-- Select Class Stream --</option>
                                @if(isset($gradeLevels) && $gradeLevels->count() > 0)
                                    @foreach($gradeLevels as $grade)
                                        <optgroup label="{{ $grade->name }}">
                                            @foreach($grade->schoolClasses as $class)
                                                <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                                    {{ $class->class_name }} ({{ $class->class_code }}) — {{ $class->students->count() }}/{{ $class->capacity }} enrolled
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                @else
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}" {{ (old('class_id') == $class->id || old('grade') == $class->class_name) ? 'selected' : '' }}>
                                            {{ $class->class_name }} ({{ $class->class_code }})
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Enrolls student directly into the target class stream roster.</p>
                        </div>
                    </div>
                </div>

                {{-- SECTION 2: Identity & Personal Details --}}
                <div class="space-y-4">
                    <div class="flex items-center">
                        <span class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 mr-3 flex items-center">
                            <i class="fa-solid fa-id-card text-sm mr-2"></i> 2. Personal Details
                        </span>
                        <div class="flex-grow border-t border-slate-200 dark:border-slate-700"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                                First Name(s) <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="Enter first name"
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="surname" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                                Surname <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="surname" id="surname" value="{{ old('surname') }}" required placeholder="Enter surname"
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="date_of_birth" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                                Date of Birth <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth') }}" required
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="gender" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                                Gender <span class="text-rose-500">*</span>
                            </label>
                            <select name="gender" id="gender" required
                                    class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label for="national_id" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                                National ID / Birth Certificate <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="national_id" id="national_id" value="{{ old('national_id') }}" required placeholder="ID number or birth entry number"
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>

                {{-- SECTION 3: Contact & Address --}}
                <div class="space-y-4">
                    <div class="flex items-center">
                        <span class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 mr-3 flex items-center">
                            <i class="fa-solid fa-address-book text-sm mr-2"></i> 3. Contact & Location
                        </span>
                        <div class="flex-grow border-t border-slate-200 dark:border-slate-700"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="phone" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                                Primary Guardian Phone <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone') }}" required placeholder="+263..."
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="emergency_contact" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                                Secondary Emergency Contact
                            </label>
                            <input type="text" name="emergency_contact" id="emergency_contact" value="{{ old('emergency_contact') }}" placeholder="Guardian Name & Alternative Phone"
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div class="md:col-span-2">
                            <label for="address" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                                Residential Address
                            </label>
                            <textarea name="address" id="address" rows="2" placeholder="House No, Street, Suburb, City"
                                      class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('address') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Automated Credentials Box --}}
                <div class="p-4 rounded-xl bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-200/80 dark:border-indigo-800/50 flex items-start space-x-3 text-xs">
                    <i class="fa-solid fa-wand-magic-sparkles text-indigo-600 dark:text-indigo-400 text-lg mt-0.5 shrink-0"></i>
                    <div>
                        <h4 class="font-bold text-indigo-950 dark:text-indigo-200">System Automated Account Setup</h4>
                        <p class="text-slate-600 dark:text-slate-400 mt-0.5 leading-relaxed">
                            A unique <strong>Student Number</strong> and portal account will be automatically generated upon registration. The default portal login password will be set to <code>surname123</code> in lowercase.
                        </p>
                    </div>
                </div>

                {{-- Form Actions --}}
                <div class="pt-4 border-t border-slate-200/80 dark:border-slate-700/60 flex items-center justify-between">
                    <a href="{{ route('students.index') }}" 
                       class="px-4 py-2.5 text-xs font-bold rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-6 py-2.5 text-xs font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 transition-all shadow-sm hover:shadow">
                        <i class="fa-solid fa-circle-check mr-2"></i> Complete Registration
                    </button>
                </div>

            </form>
        </div>

    </div>
@endsection
