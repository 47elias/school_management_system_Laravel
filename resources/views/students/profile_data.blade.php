{{-- resources/views/students/profile_data.blade.php --}}
<div class="space-y-5 text-slate-200 text-xs">
    
    {{-- Header Banner --}}
    <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 flex items-center space-x-4">
        <img src="{{ route('students.view_face', $student->id) }}" 
             x-on:error="$el.src = '{{ asset('img/default-avatar.png') }}'" 
             alt="{{ $student->name }}" 
             class="w-16 h-16 rounded-xl object-cover border border-indigo-500/30 bg-slate-800 shrink-0">
        
        <div class="space-y-1">
            <h4 class="text-base font-bold text-white">{{ $student->surname }}, {{ $student->name }}</h4>
            <p class="font-mono text-[11px] text-indigo-300">ID: {{ $student->student_number }}</p>
            <div class="flex items-center space-x-2">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                    Grade: {{ $student->grade }}
                </span>
                @if($student->status == 'active')
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        ACTIVE
                    </span>
                @else
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                        INACTIVE
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Details Grid --}}
    <div class="grid grid-cols-2 gap-3">
        <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 space-y-0.5">
            <span class="text-slate-400 font-medium text-[10px] block uppercase">Date of Birth</span>
            <span class="font-bold text-slate-100">
                {{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('d M Y') : 'N/A' }}
            </span>
        </div>

        <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 space-y-0.5">
            <span class="text-slate-400 font-medium text-[10px] block uppercase">Gender</span>
            <span class="font-bold text-slate-100">{{ $student->gender }}</span>
        </div>

        <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 space-y-0.5">
            <span class="text-slate-400 font-medium text-[10px] block uppercase">National ID</span>
            <span class="font-mono font-bold text-indigo-300">{{ $student->national_id ?? 'N/A' }}</span>
        </div>

        <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 space-y-0.5">
            <span class="text-slate-400 font-medium text-[10px] block uppercase">Guardian Phone</span>
            <span class="font-bold text-slate-100">{{ $student->phone ?? 'N/A' }}</span>
        </div>
    </div>

    {{-- Emergency & Address --}}
    <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800 space-y-2">
        <div>
            <span class="text-slate-400 font-medium text-[10px] block uppercase">Emergency Contact</span>
            <span class="font-bold text-slate-100">{{ $student->emergency_contact ?? 'N/A' }}</span>
        </div>
        <div class="pt-2 border-t border-slate-800/80">
            <span class="text-slate-400 font-medium text-[10px] block uppercase mb-0.5">Residential Address</span>
            <p class="text-slate-200 leading-relaxed">{{ $student->address ?? 'No address provided.' }}</p>
        </div>
    </div>

    {{-- Footer Actions --}}
    <div class="pt-2 flex items-center justify-end space-x-2">
        <a href="{{ route('students.edit', $student->id) }}" 
           class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition-colors">
            <i class="fa-solid fa-pen-to-square mr-1.5"></i> Edit Student
        </a>
    </div>

</div>