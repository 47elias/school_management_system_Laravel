<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Exam Gatekeeper Verification | {{ env('SCHOOL_ACRONYM') }}</title>
    @include('components.adminlte')
    @include('components.scripts')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        body { font-family: 'Inter', sans-serif !important; }
        .box { border-top: 3px solid #3c8dbc; border-radius: 5px; }

        /* Match the "Fee Structure" style Info Bar Box */
        .info-context-box { background: #ebf3f9; border: 1px solid #d2d6de; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }

        /* Video Stream Container Settings */
        #video-viewport-wrapper {
            position: relative;
            background: #111111;
            border-radius: 4px;
            overflow: hidden;
            aspect-ratio: 4/3;
            max-width: 100%;
            border: 1px solid #d2d6de;
        }
        #webcam-feed-source {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1);
        }

        /* Laser Boundary Targeting Lines */
        .scanner-hud-overlay {
            position: absolute;
            inset: 15%;
            border: 2px dashed #3c8dbc;
            border-radius: 6px;
            pointer-events: none;
            transition: all 0.3s ease;
        }
        .processing-biometrics .scanner-hud-overlay {
            border-color: #f39c12;
            animation: target-pulse 1.5s infinite;
        }
        .match-success .scanner-hud-overlay {
            border-color: #00a65a;
            background-color: rgba(0, 166, 90, 0.12);
        }
        .match-failed .scanner-hud-overlay {
            border-color: #dd4b39;
            background-color: rgba(221, 75, 57, 0.12);
        }
        @keyframes target-pulse {
            0% { opacity: 0.4; }
            50% { opacity: 1; }
            100% { opacity: 0.4; }
        }

        /* Diagnostic Badge Details */
        .biometric-avatar-frame {
            width: 115px;
            height: 115px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #00a65a;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .font-mono-data { font-family: 'Monaco', monospace; font-weight: 700; }
        .diagnostic-card-wrapper { min-height: 405px; }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')

        <div class="content-wrapper">
            {{-- PAGE HEADER CONTEXT --}}
            <section class="content-header">
                <h1>
                    Biometric Gatekeeper
                    <small>Exam Authentication Terminal</small>
                </h1>
            </section>

            {{-- MAIN WORKSPACE CONTENT --}}
            <section class="content">

                {{-- 1. SELECTED EXAM DESCRIPTION DETAILS CONTEXT BAR --}}
                <div class="box info-context-box">
                    <div class="box-body">
                        <span class="text-bold text-blue" style="font-size: 14px;">
                            <i class="fa fa-info-circle"></i> Active Verification Session:
                        </span>
                        <span class="margin-left-5 text-bold text-black" style="font-size: 14px; margin-left: 5px;">
                            {{ $exam->exam_name }} &rarr;
                            <span class="label label-primary">{{ $exam->subject?->subject_name }}</span>
                        </span>
                        <span class="pull-right hidden-xs text-muted text-sm">
                            Scheduled Date: <b>{{ \Carbon\Carbon::parse($exam->exam_date)->format('d M, Y') }}</b>
                        </span>
                    </div>
                </div>

                <div class="row">

                    {{-- 2. CAMERA VIEWER MODULE FEED --}}
                    <div class="col-md-7">
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-camera text-blue"></i> Live Optical Scanner Feed</h3>
                            </div>

                            <div class="box-body">
                                <div id="video-viewport-wrapper" class="margin-bottom">
                                    <video id="webcam-feed-source" autoplay playsinline muted></video>
                                    <canvas id="snapshot-canvas-buffer" style="display: none;" width="640" height="480"></canvas>
                                    <div class="scanner-hud-overlay" id="interactive-hud"></div>
                                </div>

                                <button id="trigger-scan-btn" class="btn btn-primary btn-lg btn-block btn-flat text-bold">
                                    <i class="fa fa-refresh fa-spin hidden" id="processing-spinner"></i>
                                    <span id="btn-label-text"><i class="fa fa-eye"></i> CAPTURE & ANALYZE BIOMETRICS</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- 3. INTERACTIVE LOG/DIAGNOSTIC LOG MONITOR PANEL --}}
                    <div class="col-md-5">
                        <div class="box box-solid bg-gray-light diagnostic-card-wrapper">
                            <div class="box-header with-border bg-navy">
                                <h3 class="box-title text-white"><i class="fa fa-desktop"></i> Authentication Output Console</h3>
                            </div>

                            <div class="box-body" id="console-display-body">

                                {{-- CONSOLE: DEFAULT IDLE SCREEN --}}
                                <div id="panel-state-idle" class="text-center text-muted" style="padding-top: 85px;">
                                    <i class="fa fa-user-circle-o" style="font-size: 65px; opacity: 0.15;"></i>
                                    <h4 class="text-bold text-black" style="margin-top: 15px;">Awaiting Scanning Sequence</h4>
                                    <p class="text-sm text-muted" style="padding: 0 30px;">Position the student cleanly inside the camera view overlay grid, then press the biometrics verification analyze trigger button.</p>
                                </div>

                                {{-- CONSOLE: IDENTITY COUPLING SUCCESS --}}
                                <div id="panel-state-success" class="text-center hidden" style="padding-top: 25px;">
                                    <div class="margin-bottom">
                                        <img id="out-avatar" src="" class="biometric-avatar-frame" alt="Verified Profile Target Photo">
                                    </div>
                                    <span class="label label-success text-bold" style="font-size: 12px; padding: 4px 10px; letter-spacing: 0.5px;">ENTRY AUTHORIZED</span>
                                    <h2 id="out-fullname" class="text-black text-bold" style="margin-top: 8px; margin-bottom: 2px;"></h2>
                                    <p id="out-idnumber" class="text-blue font-mono-data" style="font-size: 14px; margin-bottom: 15px;"></p>

                                    <div class="border-top text-muted text-xs" style="padding-top: 12px; border-top: 1px dashed #ddd;">
                                        Attendance Verification Stamp: <b id="out-timestamp" class="text-black"></b>
                                    </div>
                                </div>

                                {{-- CONSOLE: EXCEPTION REJECTION BANNER --}}
                                <div id="panel-state-error" class="text-center hidden" style="padding-top: 45px;">
                                    <div class="text-danger margin-bottom">
                                        <i class="fa fa-warning" style="font-size: 60px;"></i>
                                    </div>
                                    <span id="out-error-badge" class="label label-danger text-bold" style="font-size: 11px; padding: 4px 10px;">VERIFICATION BLOCKED</span>
                                    <h3 class="text-bold text-black" id="out-error-title" style="margin-top: 10px;">Identity Mismatch</h3>
                                    <p class="text-muted text-sm" id="out-error-message" style="padding: 5px 30px;"></p>
                                    <button class="btn btn-default btn-xs border-red margin-top" style="margin-top: 10px;" onclick="clearConsoleToIdle()">Reset Gateway Monitor</button>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
            </section>
        </div>

        @include('layouts.footer')
    </div>

    {{-- INTERACTIVE WEBCAM CONTROL LOGIC --}}
    <script>
        const videoElement = document.getElementById('webcam-feed-source');
        const canvasElement = document.getElementById('snapshot-canvas-buffer');
        const hudOverlay = document.getElementById('interactive-hud');
        const actionButton = document.getElementById('trigger-scan-btn');
        const spinnerIcon = document.getElementById('processing-spinner');
        const labelsContainer = document.getElementById('btn-label-text');

        // Layout States Panels
        const idlePanel = document.getElementById('panel-state-idle');
        const successPanel = document.getElementById('panel-state-success');
        const errorPanel = document.getElementById('panel-state-error');

        // Request streaming permissions dynamically on document mounts
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480 } })
                .then(function(mediaStream) {
                    videoElement.srcObject = mediaStream;
                    videoElement.play();
                })
                .catch(function(hardwareError) {
                    console.error("Camera access failed: ", hardwareError);
                    alert("Unable to securely access the video media hardware layer. Please confirm explicit site security configurations (Localhost or active SSL HTTPS channels).");
                });
        }

        // Action Trigger Button Bind Handler
        actionButton.addEventListener('click', function() {
            setConsoleLoadingState(true);

            // Mirror current video output layer onto our hidden drawing canvas buffer frame
            const context = canvasElement.getContext('2d');
            context.drawImage(videoElement, 0, 0, 640, 480);
            const base64PayloadString = canvasElement.toDataURL('image/jpeg');

            // Dispatch payload asynchronously back to your defined Laravel TeacherController context path
            fetch("{{ route('exams.verify_face') }}", {
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
                    // Populate success view nodes
                    document.getElementById('out-avatar').src = bundle.data.student.photo_url;
                    document.getElementById('out-fullname').innerText = bundle.data.student.name;
                    document.getElementById('out-idnumber').innerText = bundle.data.student.student_number;
                    document.getElementById('out-timestamp').innerText = bundle.data.student.verified_time;

                    hudOverlay.className = "scanner-hud-overlay match-success";
                    successPanel.classList.remove('hidden');

                    // Reset screen back to scan mode after an active display pause window
                    setTimeout(clearConsoleToIdle, 3500);
                } else {
                    // Check system block codes (402 = financial halt status configurations)
                    if (bundle.status === 402) {
                        document.getElementById('out-error-badge').className = "label label-warning text-bold";
                        document.getElementById('out-error-title').innerText = "Financial Lock Detected";
                    } else {
                        document.getElementById('out-error-badge').className = "label label-danger text-bold";
                        document.getElementById('out-error-title').innerText = "Identity Rejection";
                    }

                    document.getElementById('out-error-message').innerText = bundle.data.message || "Failed biometric signature mapping confirmation indexes matches.";
                    hudOverlay.className = "scanner-hud-overlay match-failed";
                    errorPanel.classList.remove('hidden');
                }
            })
            .catch(networkFault => {
                setConsoleLoadingState(false);
                purgePanelVisibilities();
                document.getElementById('out-error-badge').className = "label label-danger text-bold";
                document.getElementById('out-error-title').innerText = "System Exception";
                document.getElementById('out-error-message').innerText = "Critical connection error communicating processing matrices parameter chunks back to backend services.";
                errorPanel.classList.remove('hidden');
            });
        });

        function setConsoleLoadingState(isLoading) {
            if (isLoading) {
                actionButton.disabled = true;
                spinnerIcon.classList.remove('hidden');
                labelsContainer.innerText = " COMPUTING BIOMETRIC HASH MATRICES...";
                hudOverlay.className = "scanner-hud-overlay processing-biometrics";
            } else {
                actionButton.disabled = false;
                spinnerIcon.classList.add('hidden');
                labelsContainer.innerHTML = '<i class="fa fa-eye"></i> CAPTURE & ANALYZE BIOMETRICS';
            }
        }

        function purgePanelVisibilities() {
            idlePanel.classList.add('hidden');
            successPanel.classList.add('hidden');
            errorPanel.classList.add('hidden');
        }

        function clearConsoleToIdle() {
            purgePanelVisibilities();
            hudOverlay.className = "scanner-hud-overlay";
            idlePanel.classList.remove('hidden');
        }
    </script>
</body>
</html>
