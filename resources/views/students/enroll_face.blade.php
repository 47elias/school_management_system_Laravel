<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Enroll Biometrics | {{ env('SCHOOL_ACRONYM') }}</title>
    @include('components.adminlte')
    @include('components.scripts')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        .box { border-top: 3px solid #3c8dbc; }
        #video-viewport { position: relative; background: #000; border-radius: 4px; overflow: hidden; aspect-ratio: 4/3; }
        #webcam-feed { width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1); }
        .hud-overlay { position: absolute; inset: 20%; border: 2px dashed #00a65a; border-radius: 8px; pointer-events: none; }
        #loading-overlay { position: absolute; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); color:#fff; display:flex; align-items:center; justify-content:center; z-index:10; }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        @include('layouts.layout_separator')

        <div class="content-wrapper">
            <section class="content-header">
                <h1>Biometric Enrollment <small>For: <b>{{ $student->surname }}, {{ $student->name }}</b></small></h1>
            </section>

            <section class="content">
                <div class="row">
                    <div class="col-md-6">
                        <div class="box box-success">
                            <div class="box-body">
                                <div id="video-viewport">
                                    <div id="loading-overlay"><i class="fa fa-spinner fa-spin"></i> Injecting AI Framework...</div>
                                    <video id="webcam-feed" autoplay playsinline muted></video>
                                    <div class="hud-overlay"></div>
                                </div>
                                <button id="save-btn" class="btn btn-success btn-lg btn-block btn-flat" style="margin-top:15px;" disabled>
                                    <i class="fa fa-spinner fa-spin"></i> LOADING AI...
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
    <script src="{{ asset('js/face-api.min.js') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', async () => {
        const video = document.getElementById('webcam-feed');
        const saveBtn = document.getElementById('save-btn');
        const loadingOverlay = document.getElementById('loading-overlay');

        // Force a check until the library is found
        const waitForFaceAPI = setInterval(async () => {
            if (typeof faceapi !== 'undefined') {
                clearInterval(waitForFaceAPI);
                console.log("FaceAPI confirmed loaded.");

                try {
                    // Load Models
                    await faceapi.nets.ssdMobilenetv1.loadFromUri('/models');
                    await faceapi.nets.faceLandmark6dNet.loadFromUri('/models');
                    await faceapi.nets.faceRecognitionNet.loadFromUri('/models');

                    const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                    video.srcObject = stream;

                    loadingOverlay.style.display = 'none';
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="fa fa-save"></i> SAVE BIOMETRIC PROFILE';
                } catch (err) {
                    loadingOverlay.innerHTML = '<span class="text-red">Model Error: ' + err.message + '</span>';
                }
            }
        }, 200);
    });

    // Save Logic
    document.getElementById('save-btn').addEventListener('click', async function() {
        this.disabled = true;
        this.innerHTML = '<i class="fa fa-spinner fa-spin"></i> ENROLLING...';

        const detection = await faceapi.detectSingleFace(document.getElementById('webcam-feed'))
            .withFaceLandmarks()
            .withFaceDescriptor();

        if (!detection) {
            alert("No face detected!");
            this.disabled = false;
            this.innerHTML = '<i class="fa fa-save"></i> SAVE BIOMETRIC PROFILE';
            return;
        }

        $.ajax({
            url: "{{ route('students.store_face', $student->id) }}",
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                face_descriptor: JSON.stringify(Array.from(detection.descriptor))
            },
            success: () => { alert("Enrolled!"); window.location.href = "{{ route('students.index') }}"; },
            error: () => { alert("Error."); this.disabled = false; }
        });
    });
</script>
</body>
</html>
