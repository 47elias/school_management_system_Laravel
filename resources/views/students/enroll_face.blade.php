<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Enroll Biometrics | {{ env('SCHOOL_ACRONYM') }}</title>
    @include('components.adminlte')
    @include('components.scripts')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        body { font-family: 'Inter', sans-serif !important; }
        .box { border-top: 3px solid #3c8dbc; border-radius: 5px; }
        #video-viewport { position: relative; background: #000; border-radius: 4px; overflow: hidden; aspect-ratio: 4/3; max-width: 100%; }
        #webcam-feed { width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1); }
        .hud-overlay { position: absolute; inset: 20%; border: 2px dashed #00a65a; border-radius: 8px; pointer-events: none; }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')

        <div class="content-wrapper">
            <section class="content-header">
                <h1>
                    Biometric Enrollment
                    <small>Capturing profile for: <b>{{ $student->surname }}, {{ $student->name }}</b></small>
                </h1>
            </section>

            <section class="content">
                <div class="row">
                    <div class="col-md-6">
                        <div class="box box-success">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-camera"></i> Live Capture</h3>
                            </div>
                            <div class="box-body">
                                <div id="video-viewport">
                                    <video id="webcam-feed" autoplay playsinline muted></video>
                                    <canvas id="canvas-buffer" style="display: none;" width="640" height="480"></canvas>
                                    <div class="hud-overlay"></div>
                                </div>
                                <button id="save-btn" class="btn btn-success btn-lg btn-block btn-flat margin-top">
                                    <i class="fa fa-save"></i> SAVE BIOMETRIC PROFILE
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="box box-solid">
                            <div class="box-body">
                                <h4>Instructions</h4>
                                <ul class="text-muted">
                                    <li>Ensure the student is facing the camera directly.</li>
                                    <li>Ensure good lighting on the student's face.</li>
                                    <li>Remove glasses or hats if necessary for higher accuracy.</li>
                                    <li>Click "Save Biometric Profile" to register the data.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        @include('layouts.footer')
    </div>

    <script>
        const video = document.getElementById('webcam-feed');
        const canvas = document.getElementById('canvas-buffer');
        const saveBtn = document.getElementById('save-btn');

        // Initialize Camera
        async function initCamera() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: { width: 640, height: 480 },
                    audio: false
                });
                video.srcObject = stream;
            } catch (err) {
                console.error("Camera Error:", err);
                alert("Camera access denied or not available. Please check permissions.");
            }
        }
        initCamera();

        // Save Logic
        saveBtn.addEventListener('click', function() {
            // Disable button to prevent double-submissions
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> PROCESSING...';

            const context = canvas.getContext('2d');
            context.drawImage(video, 0, 0, 640, 480);
            const base64Data = canvas.toDataURL('image/jpeg');

            $.ajax({
                url: "{{ route('students.store_face', $student->id) }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    face_image: base64Data
                },
                success: function(res) {
                    alert("Biometric profile saved successfully!");
                    window.location.href = "{{ route('students.index') }}";
                },
                error: function(err) {
                    console.error(err);
                    alert("Failed to save biometric data. Please try again.");
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="fa fa-save"></i> SAVE BIOMETRIC PROFILE';
                }
            });
        });
    </script>
</body>
</html>
