{{-- resources/views/teacher/exams/verify.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Check Exam Attendance | ' . $exam->exam_name)

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-shield-halved text-indigo-600 dark:text-indigo-400"></i>
                <span>Check Exam Attendance</span>
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Use the camera to check a student before the exam.</p>
        </div>

        <a href="{{ route('teacher.exams.index') }}" 
           class="inline-flex items-center px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 text-xs font-bold transition-all border border-slate-200 dark:border-slate-700 shadow-xs">
            <i class="fa-solid fa-arrow-left mr-2"></i> Back to Exams
        </a>
    </div>
@endsection

@section('content')
    <div class="space-y-6">

        {{-- Active Session Context Header --}}
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/60 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/50 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-circle-info text-sm"></i>
                </div>
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Exam</span>
                    <h4 class="text-sm font-bold text-slate-800 dark:text-slate-100">
                        {{ $exam->exam_name }} &rarr; <span class="text-indigo-600 dark:text-indigo-400">{{ $exam->subject->subject_name ?? $exam->subject->name ?? 'N/A' }}</span>
                    </h4>
                </div>
            </div>

            <div class="text-xs text-slate-500 dark:text-slate-400 font-medium shrink-0">
                Exam Date: <strong class="text-slate-700 dark:text-slate-200 font-mono">{{ \Carbon\Carbon::parse($exam->exam_date)->format('d M, Y') }}</strong>
            </div>
        </div>

        {{-- Main Scanner Workspace Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- Camera Feed Column (7 cols) --}}
            <div class="lg:col-span-7 bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60 pb-3">
                    <h3 class="font-bold text-sm text-slate-800 dark:text-slate-100 flex items-center space-x-2">
                        <i class="fa-solid fa-camera text-indigo-500"></i>
                        <span>Camera</span>
                    </h3>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span> LIVE
                    </span>
                </div>

                {{-- Video Stream Viewport --}}
                <div class="relative w-full aspect-4/3 bg-slate-900 rounded-xl overflow-hidden border border-slate-700/80">
                    <video id="webcam-feed-source" autoplay playsinline muted class="w-full h-full object-cover scale-x-[-1]"></video>
                    <canvas id="snapshot-canvas-buffer" class="hidden" width="640" height="480"></canvas>

                    {{-- HUD Overlay Frame --}}
                    <div id="interactive-hud" class="absolute inset-[15%] border-2 border-dashed border-indigo-400 rounded-2xl pointer-events-none transition-all duration-300"></div>
                </div>

                {{-- Trigger Button --}}
                <button id="trigger-scan-btn" 
                        class="w-full py-3.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs uppercase tracking-wider transition-all shadow-xs flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-circle-notch fa-spin hidden" id="processing-spinner"></i>
                    <span id="btn-label-text" class="flex items-center">
                        <i class="fa-solid fa-eye mr-2"></i> Check Student
                    </span>
                </button>
            </div>

            {{-- Diagnostic Console Panel (5 cols) --}}
            <div class="lg:col-span-5 bg-slate-900 text-white p-5 rounded-2xl border border-slate-800 shadow-xs flex flex-col justify-between min-h-[420px]">
                
                <div class="border-b border-slate-800 pb-3 flex items-center justify-between">
                    <h3 class="font-bold text-xs uppercase tracking-wider text-slate-300 flex items-center space-x-2">
                        <i class="fa-solid fa-desktop text-indigo-400"></i>
                        <span>Authentication Console</span>
                    </h3>
                </div>

                <div id="console-display-body" class="my-auto py-6">

                    {{-- Idle State Screen --}}
                    <div id="panel-state-idle" class="text-center space-y-3">
                        <div class="w-16 h-16 rounded-full bg-slate-800 text-slate-600 flex items-center justify-center mx-auto">
                            <i class="fa-solid fa-user-check text-2xl"></i>
                        </div>
                        <h4 class="text-sm font-bold text-slate-200">Awaiting Scanning Sequence</h4>
                        <p class="text-xs text-slate-400 max-w-xs mx-auto leading-relaxed">
                            Position student within camera overlay guidelines, then press the biometrics verification trigger.
                        </p>
                    </div>

                    {{-- Success State Screen --}}
                    <div id="panel-state-success" class="text-center space-y-3 hidden">
                        <img id="out-avatar" src="" class="w-24 h-24 rounded-full object-cover mx-auto border-4 border-emerald-500 shadow-md" alt="Verified Student Avatar">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                            <i class="fa-solid fa-circle-check mr-1.5"></i> Entry Authorized
                        </span>
                        <h2 id="out-fullname" class="text-lg font-black text-white"></h2>
                        <p id="out-idnumber" class="text-xs font-mono font-bold text-indigo-400"></p>
                        <p class="text-[11px] text-slate-400 border-t border-slate-800 pt-3">
                            Verified Stamp: <strong id="out-timestamp" class="text-slate-200 font-mono"></strong>
                        </p>
                    </div>

                    {{-- Error State Screen --}}
                    <div id="panel-state-error" class="text-center space-y-3 hidden">
                        <div class="w-16 h-16 rounded-full bg-rose-500/20 text-rose-500 flex items-center justify-center mx-auto border border-rose-500/30">
                            <i class="fa-solid fa-triangle-exclamation text-2xl"></i>
                        </div>
                        <span id="out-error-badge" class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-500/20 text-rose-400 border border-rose-500/30">
                            Check could not be completed
                        </span>
                        <h3 id="out-error-title" class="text-sm font-bold text-white">Identity Mismatch</h3>
                        <p id="out-error-message" class="text-xs text-slate-400 max-w-xs mx-auto"></p>
                        <button type="button" onclick="clearConsoleToIdle()" class="px-3 py-1.5 text-xs font-bold rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors">
                            Start Again
                        </button>
                    </div>

                </div>

                <div class="border-t border-slate-800 pt-3 text-[10px] text-slate-500 flex items-center justify-between font-mono">
                    <span>Student Check</span>
                    <span>SSL Secure API</span>
                </div>

            </div>

        </div>

    </div>

    {{-- Script Handler --}}
    <script>
        const videoElement = document.getElementById('webcam-feed-source');
        const canvasElement = document.getElementById('snapshot-canvas-buffer');
        const hudOverlay = document.getElementById('interactive-hud');
        const actionButton = document.getElementById('trigger-scan-btn');
        const spinnerIcon = document.getElementById('processing-spinner');
        const labelsContainer = document.getElementById('btn-label-text');

        const idlePanel = document.getElementById('panel-state-idle');
        const successPanel = document.getElementById('panel-state-success');
        const errorPanel = document.getElementById('panel-state-error');

        // Request streaming permissions
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480 } })
                .then(function(mediaStream) {
                    videoElement.srcObject = mediaStream;
                    videoElement.play();
                })
                .catch(function(hardwareError) {
                    console.error("Camera access failed: ", hardwareError);
                    alert("Unable to access camera. Please ensure permissions are granted.");
                });
        }

        actionButton.addEventListener('click', function() {
            setConsoleLoadingState(true);

            const context = canvasElement.getContext('2d');
            context.drawImage(videoElement, 0, 0, 640, 480);
            const base64PayloadString = canvasElement.toDataURL('image/jpeg');

            fetch("{{ route('teacher.exams.verify_face') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    exam_id: "{{ $exam->id }}",
                    face_image: base64PayloadString
                })
            })
            .then(res => res.json().then(jsonPayload => ({ status: res.status, data: jsonPayload })))
            .then(bundle => {
                setConsoleLoadingState(false);
                purgePanelVisibilities();

                if (bundle.status === 200) {
                    document.getElementById('out-avatar').src = bundle.data.student.photo_url;
                    document.getElementById('out-fullname').innerText = bundle.data.student.name;
                    document.getElementById('out-idnumber').innerText = bundle.data.student.student_number;
                    document.getElementById('out-timestamp').innerText = bundle.data.student.verified_time;

                    hudOverlay.className = "absolute inset-[15%] border-2 border-emerald-500 bg-emerald-500/10 rounded-2xl pointer-events-none transition-all duration-300";
                    successPanel.classList.remove('hidden');

                    setTimeout(clearConsoleToIdle, 4000);
                } else {
                    if (bundle.status === 402) {
                        document.getElementById('out-error-badge').className = "inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-500/20 text-amber-400 border border-amber-500/30";
                        document.getElementById('out-error-title').innerText = "Financial Clearance Lock";
                    } else {
                        document.getElementById('out-error-badge').className = "inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-500/20 text-rose-400 border border-rose-500/30";
                        document.getElementById('out-error-title').innerText = "Student not recognised";
                    }

                    document.getElementById('out-error-message').innerText = bundle.data.message || "Failed biometric signature match.";
                    hudOverlay.className = "absolute inset-[15%] border-2 border-rose-500 bg-rose-500/10 rounded-2xl pointer-events-none transition-all duration-300";
                    errorPanel.classList.remove('hidden');
                }
            })
            .catch(() => {
                setConsoleLoadingState(false);
                purgePanelVisibilities();
                document.getElementById('out-error-title').innerText = "System Exception";
                document.getElementById('out-error-message').innerText = "Error communicating with biometric verification backend.";
                errorPanel.classList.remove('hidden');
            });
        });

        function setConsoleLoadingState(isLoading) {
            if (isLoading) {
                actionButton.disabled = true;
                spinnerIcon.classList.remove('hidden');
                labelsContainer.innerText = " Checking student...";
                hudOverlay.className = "absolute inset-[15%] border-2 border-amber-400 animate-pulse rounded-2xl pointer-events-none transition-all duration-300";
            } else {
                actionButton.disabled = false;
                spinnerIcon.classList.add('hidden');
                labelsContainer.innerHTML = '<i class="fa-solid fa-eye mr-2"></i> Check Student';
            }
        }

        function purgePanelVisibilities() {
            idlePanel.classList.add('hidden');
            successPanel.classList.add('hidden');
            errorPanel.classList.add('hidden');
        }

        function clearConsoleToIdle() {
            purgePanelVisibilities();
            hudOverlay.className = "absolute inset-[15%] border-2 border-dashed border-indigo-400 rounded-2xl pointer-events-none transition-all duration-300";
            idlePanel.classList.remove('hidden');
        }
    </script>
@endsection
