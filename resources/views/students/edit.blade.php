{{-- resources/views/students/edit.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Edit Student | ' . $student->name)

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Edit Student Profile</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Modify student details for <strong class="text-slate-700 dark:text-slate-200">{{ $student->name }} {{ $student->surname }}</strong></p>
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
                        <h4 class="text-xs font-bold uppercase tracking-wider">Update Failed! Please check fields:</h4>
                        <ul class="mt-1.5 space-y-1 list-disc list-inside text-xs font-medium">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        {{-- Quick Information Ribbon --}}
        <div class="p-4 rounded-2xl bg-amber-50/80 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-800/50 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs">
            <div class="flex items-center space-x-3">
                <span class="p-2.5 rounded-xl bg-amber-500 text-white font-bold">
                    <i class="fa-solid fa-id-card text-sm"></i>
                </span>
                <div>
                    <p class="font-bold text-slate-800 dark:text-slate-100">Student Ref: <span class="font-mono">{{ $student->student_number }}</span></p>
                    <p class="text-slate-500 dark:text-slate-400 mt-0.5">Portal Email: {{ $student->email ?? 'N/A' }}</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <span class="text-slate-500 dark:text-slate-400">Registered: {{ $student->created_at ? $student->created_at->format('d M Y') : 'N/A' }}</span>
                @if($student->status == 'active')
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50">ACTIVE</span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/50">INACTIVE</span>
                @endif
            </div>
        </div>

        {{-- Form Card --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
            
            <form method="POST" action="{{ route('students.update', $student->id) }}" class="p-6 space-y-8">
                @csrf
                @method('PUT')

                {{-- SECTION 1: Academic Placement & Stream Assignment --}}
                <div class="space-y-4">
                    <div class="flex items-center">
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 mr-3 flex items-center">
                            <i class="fa-solid fa-graduation-cap text-sm mr-2"></i> Academic Placement & Status
                        </span>
                        <div class="flex-grow border-t border-slate-200 dark:border-slate-700"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="class_id" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Class Stream</label>
                            <select name="class_id" id="class_id" required
                                    class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500 font-semibold">
                                @if(isset($gradeLevels) && $gradeLevels->count() > 0)
                                    @foreach($gradeLevels as $grade)
                                        <optgroup label="{{ $grade->name }}">
                                            @foreach($grade->schoolClasses as $class)
                                                <option value="{{ $class->id }}" {{ (old('class_id', $student->class_id) == $class->id || old('grade', $student->grade) == $class->class_name) ? 'selected' : '' }}>
                                                    {{ $class->class_name }} ({{ $class->class_code }})
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                @else
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}" {{ (old('class_id', $student->class_id) == $class->id || old('grade', $student->grade) == $class->class_name) ? 'selected' : '' }}>
                                            {{ $class->class_name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <div>
                            <label for="term_id" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Current Academic Term</label>
                            <select name="term_id" id="term_id" required
                                    class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500 font-semibold">
                                @foreach($terms as $term)
                                    <option value="{{ $term->id }}" {{ old('term_id', $student->term_id) == $term->id ? 'selected' : '' }}>
                                        {{ $term->term_name }} ({{ $term->academic_year }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="status" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Account Status</label>
                            <select name="status" id="status"
                                    class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500 font-semibold">
                                <option value="active" {{ old('status', $student->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $student->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- SECTION 2: Identity & Personal Details --}}
                <div class="space-y-4">
                    <div class="flex items-center">
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 mr-3 flex items-center">
                            <i class="fa-solid fa-user text-sm mr-2"></i> Personal Information
                        </span>
                        <div class="flex-grow border-t border-slate-200 dark:border-slate-700"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">First Name(s)</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $student->name) }}" required
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500">
                        </div>

                        <div>
                            <label for="surname" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Surname</label>
                            <input type="text" name="surname" id="surname" value="{{ old('surname', $student->surname) }}" required
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500">
                        </div>

                        <div>
                            <label for="date_of_birth" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Date of Birth</label>
                            <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth', $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('Y-m-d') : '') }}" required
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500">
                        </div>

                        <div>
                            <label for="gender" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Gender</label>
                            <select name="gender" id="gender"
                                    class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500">
                                <option value="Male" {{ old('gender', $student->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender', $student->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label for="national_id" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">National ID / Birth Certificate</label>
                            <input type="text" name="national_id" id="national_id" value="{{ old('national_id', $student->national_id) }}" required
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500">
                        </div>
                    </div>
                </div>

                {{-- SECTION 3: Contact & Address --}}
                <div class="space-y-4">
                    <div class="flex items-center">
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 mr-3 flex items-center">
                            <i class="fa-solid fa-phone text-sm mr-2"></i> Contact Details
                        </span>
                        <div class="flex-grow border-t border-slate-200 dark:border-slate-700"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="phone" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Guardian Phone</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone', $student->phone) }}" required
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500">
                        </div>

                        <div>
                            <label for="emergency_contact" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Emergency Contact</label>
                            <input type="text" name="emergency_contact" id="emergency_contact" value="{{ old('emergency_contact', $student->emergency_contact) }}"
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500">
                        </div>

                        <div class="md:col-span-2">
                            <label for="address" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Residential Address</label>
                            <textarea name="address" id="address" rows="2"
                                      class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500">{{ old('address', $student->address) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="pt-4 border-t border-slate-200/80 dark:border-slate-700/60 flex items-center justify-between">
                    <a href="{{ route('students.index') }}" 
                       class="px-4 py-2.5 text-xs font-bold rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-6 py-2.5 text-xs font-bold rounded-xl text-white bg-amber-500 hover:bg-amber-600 transition-all shadow-sm hover:shadow">
                        <i class="fa-solid fa-floppy-disk mr-2"></i> Update Student Record
                    </button>
                </div>

            </form>
        </div>

    </div>
@endsection