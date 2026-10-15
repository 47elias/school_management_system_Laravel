{{-- resources/views/exams/verify.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Exam Gatekeeper Verification | ' . env('SCHOOL_ACRONYM', 'SMS'))

@section('header')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Biometric Gatekeeper</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Exam Authentication Terminal</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ Route::has('exams.index') ? route('exams.index') : url('/exams') }}" 
               class="inline-flex items-center px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all border border-slate-200 dark:border-slate-700">
                <i class="fa-solid fa-arrow-left mr-2"></i> Back to Schedule
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="space-y-6">

        {{-- Active Context Banner --}}
        <div class="p-4 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-circle-info text-indigo-500 text-base"></i>
                <span class="font-bold text-slate-800 dark:text-slate-200">Active Verification Session:</span>
                <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $exam->exam_name }}</span>
                <span class="px-2.5 py-0.5 rounded-full bg-indigo-500 text-white font-bold text-[10px]">{{ $exam->subject?->subject_name }}</span>
            </div>
            <div class="text-slate-500 dark:text-slate-400">
                Scheduled Date: <strong class="text-slate-800 dark:text-slate-200">{{ \Carbon\Carbon::parse($exam->exam_date)->format('d M, Y') }}</strong>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- Optical Scanner Feed --}}
            <div class="lg:col-span-7">
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
                    <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/80">
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                            <i class="fa-solid fa-camera text-indigo-500 mr-2.5"></i> Live Optical Scanner Feed
                        </h3>
                    </div>

                    <div class="p-6 space-y-4">
                        <div class="relative bg-slate-900 rounded-xl overflow-hidden aspect-[4/3] w-full border border-slate-700">
                            <video id="webcam-feed-source" autoplay playsinline muted class="w-full h-full object-cover scale-x-[-1]"></video>
                            <canvas id="snapshot-canvas-buffer" style="display: none;" width="640" height="480"></canvas>
                            <div id="interactive-hud" class="absolute inset-[15%] border-2 border-dashed border-indigo-500 rounded-xl pointer-events-none transition-all"></div>
                        </div>

                        <button id="trigger-scan-btn" 
                                class="w-full py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-sm flex items-center justify-center space-x-2">
                            <i id="processing-spinner" class="fa-solid fa-circle-notch fa-spin hidden"></i>
                            <span id="btn-label-text" class="flex items-center"><i class="fa-solid fa-eye mr-2"></i> CAPTURE & ANALYZE BIOMETRICS</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Authentication Output Console --}}
            <div class="lg:col-span-5">
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden min-h-[420px] transition-colors flex flex-col">
                    <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-900 text-white">
                        <h3 class="text-base font-bold flex items-center">
                            <i class="fa-solid fa-desktop mr-2.5"></i> Authentication Console
                        </h3>
                    </div>

                    <div class="p-6 flex-1 flex flex-col items-center justify-center text-center">

                        {{-- IDLE STATE --}}
                        <div id="panel-state-idle" class="space-y-3">
                            <div class="w-20 h-20 bg-slate-100 dark:bg-slate-700/50 rounded-full flex items-center justify-center mx-auto text-slate-300 dark:text-slate-600">
                                <i class="fa-solid fa-user-check text-4xl"></i>
                            </div>
                            <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200">Awaiting Scanning Sequence</h4>
                            <p class="text-xs text-slate-400 max-w-xs leading-relaxed">
                                Position the student cleanly inside the camera view overlay grid, then press the biometrics verification analyze trigger button.
                            </p>
                        </div>

                        {{-- SUCCESS STATE --}}
                        <div id="panel-state-success" class="hidden space-y-3">
                            <img id="out-avatar" src="" alt="Verified Target Photo" class="w-24 h-24 rounded-full object-cover border-4 border-emerald-500 shadow-md mx-auto">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-500 text-white">ENTRY AUTHORIZED</span>
                            <h2 id="out-fullname" class="text-lg font-bold text-slate-800 dark:text-slate-100"></h2>
                            <p id="out-idnumber" class="text-xs font-mono font-bold text-indigo-600 dark:text-indigo-400"></p>
                            <p class="text-[11px] text-slate-400 pt-3 border-t border-slate-100 dark:border-slate-700/60">
                                Stamp: <strong id="out-timestamp" class="text-slate-700 dark:text-slate-300"></strong>
                            </p>
                        </div>

                        {{-- ERROR STATE --}}
                        <div id="panel-state-error" class="hidden space-y-3">
                            <div class="w-16 h-16 bg-rose-50 dark:bg-rose-950/50 text-rose-500 rounded-full flex items-center justify-center mx-auto text-2xl">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                            </div>
                            <span id="out-error-badge" class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold bg-rose-500 text-white">VERIFICATION BLOCKED</span>
                            <h3 id="out-error-title" class="text-base font-bold text-slate-800 dark:text-slate-100">Identity Mismatch</h3>
                            <p id="out-error-message" class="text-xs text-slate-400 max-w-xs leading-relaxed"></p>
                            <button onclick="clearConsoleToIdle()" class="px-3.5 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-200 text-xs font-bold transition-colors">
                                Reset Monitor
                            </button>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>

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

        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480 } })
                .then(function(mediaStream) {
                    videoElement.srcObject = mediaStream;
                    videoElement.play();
                })
                .catch(function(hardwareError) {
                    console.error("Camera access failed: ", hardwareError);
                    alert("Unable to access camera media stream. Please verify browser permissions and SSL configuration.");
                });
        }

        actionButton.addEventListener('click', function() {
            setConsoleLoadingState(true);

            const context = canvasElement.getContext('2d');
            context.drawImage(videoElement, 0, 0, 640, 480);
            const base64PayloadString = canvasElement.toDataURL('image/jpeg');

            fetch("{{ Route::has('exams.verify_face') ? route('exams.verify_face') : url('/exams/verify-face') }}", {
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

                    hudOverlay.className = "absolute inset-[15%] border-2 border-emerald-500 bg-emerald-500/10 rounded-xl pointer-events-none transition-all";
                    successPanel.classList.remove('hidden');

                    setTimeout(clearConsoleToIdle, 3500);
                } else {
                    if (bundle.status === 402) {
                        document.getElementById('out-error-badge').className = "inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold bg-amber-500 text-white";
                        document.getElementById('out-error-title').innerText = "Financial Lock Detected";
                    } else {
                        document.getElementById('out-error-badge').className = "inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold bg-rose-500 text-white";
                        document.getElementById('out-error-title').innerText = "Identity Rejection";
                    }

                    document.getElementById('out-error-message').innerText = bundle.data.message || "Failed biometric signature mapping confirmation.";
                    hudOverlay.className = "absolute inset-[15%] border-2 border-rose-500 bg-rose-500/10 rounded-xl pointer-events-none transition-all";
                    errorPanel.classList.remove('hidden');
                }
            })
            .catch(networkFault => {
                setConsoleLoadingState(false);
                purgePanelVisibilities();
                document.getElementById('out-error-badge').className = "inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold bg-rose-500 text-white";
                document.getElementById('out-error-title').innerText = "System Exception";
                document.getElementById('out-error-message').innerText = "Connection error communicating with backend verification service.";
                errorPanel.classList.remove('hidden');
            });
        });

        function setConsoleLoadingState(isLoading) {
            if (isLoading) {
                actionButton.disabled = true;
                spinnerIcon.classList.remove('hidden');
                labelsContainer.innerText = " COMPUTING BIOMETRIC HASH MATRICES...";
                hudOverlay.className = "absolute inset-[15%] border-2 border-dashed border-amber-500 animate-pulse rounded-xl pointer-events-none transition-all";
            } else {
                actionButton.disabled = false;
                spinnerIcon.classList.add('hidden');
                labelsContainer.innerHTML = '<i class="fa-solid fa-eye mr-2"></i> CAPTURE & ANALYZE BIOMETRICS';
            }
        }

        function purgePanelVisibilities() {
            idlePanel.classList.add('hidden');
            successPanel.classList.add('hidden');
            errorPanel.classList.add('hidden');
        }

        function clearConsoleToIdle() {
            purgePanelVisibilities();
            hudOverlay.className = "absolute inset-[15%] border-2 border-dashed border-indigo-500 rounded-xl pointer-events-none transition-all";
            idlePanel.classList.remove('hidden');
        }
    </script>
@endsection