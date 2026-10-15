{{-- resources/views/students/enroll_face.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Biometric Enrollment | ' . $student->name)

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Biometric Profile Enrollment</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Student target: <strong class="text-slate-800 dark:text-slate-200">{{ $student->surname }}, {{ $student->name }}</strong> (<span class="font-mono">{{ $student->student_number }}</span>)
            </p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('students.index') }}" 
               class="inline-flex items-center px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 text-xs font-bold transition-all border border-slate-200 dark:border-slate-700">
                <i class="fa-solid fa-arrow-left mr-2"></i> Back to Student List
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="max-w-xl mx-auto space-y-6">

        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 p-6 transition-colors space-y-4">
            
            <div class="relative w-full aspect-[4/3] bg-black rounded-xl overflow-hidden shadow-inner">
                {{-- Loading Overlay --}}
                <div id="loading-overlay" class="absolute inset-0 bg-slate-950/80 text-white flex flex-col items-center justify-center space-y-2 z-10 text-xs font-bold">
                    <i class="fa-solid fa-circle-notch fa-spin text-2xl text-emerald-400"></i>
                    <span>Loading AI Recognition Engine...</span>
                </div>

                {{-- Live Camera Stream --}}
                <video id="webcam-feed" autoplay playsinline muted class="w-full h-full object-cover scale-x-[-1]"></video>

                {{-- Targeting HUD Overlay --}}
                <div class="absolute inset-[15%] border-2 border-dashed border-emerald-400 rounded-2xl pointer-events-none opacity-80 animate-pulse"></div>
            </div>

            <button id="save-btn" disabled
                    class="w-full py-3 px-4 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-sm flex items-center justify-center space-x-2">
                <i class="fa-solid fa-spinner fa-spin text-sm"></i>
                <span>INITIALIZING ENGINE...</span>
            </button>
        </div>

    </div>

    {{-- Face API Script --}}
    <script src="{{ asset('js/face-api.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const video = document.getElementById('webcam-feed');
            const saveBtn = document.getElementById('save-btn');
            const loadingOverlay = document.getElementById('loading-overlay');

            const waitForFaceAPI = setInterval(async () => {
                if (typeof faceapi !== 'undefined') {
                    clearInterval(waitForFaceAPI);

                    try {
                        await faceapi.nets.ssdMobilenetv1.loadFromUri('/models');
                        await faceapi.nets.faceLandmark6dNet.loadFromUri('/models');
                        await faceapi.nets.faceRecognitionNet.loadFromUri('/models');

                        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                        video.srcObject = stream;

                        loadingOverlay.style.display = 'none';
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = '<i class="fa-solid fa-floppy-disk mr-2"></i> SAVE BIOMETRIC PROFILE';
                    } catch (err) {
                        loadingOverlay.innerHTML = '<span class="text-rose-400 p-4 text-center">Model Error: ' + err.message + '</span>';
                    }
                }
            }, 200);
        });

        document.getElementById('save-btn').addEventListener('click', async function() {
            this.disabled = true;
            this.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin mr-2"></i> ENROLLING...';

            const detection = await faceapi.detectSingleFace(document.getElementById('webcam-feed'))
                .withFaceLandmarks()
                .withFaceDescriptor();

            if (!detection) {
                alert("No facial signature detected! Center your face in the target frame and retry.");
                this.disabled = false;
                this.innerHTML = '<i class="fa-solid fa-floppy-disk mr-2"></i> SAVE BIOMETRIC PROFILE';
                return;
            }

            fetch("{{ route('students.store_face', $student->id) }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    face_descriptor: JSON.stringify(Array.from(detection.descriptor))
                })
            })
            .then(res => res.json())
            .then(data => {
                alert("Biometric profile successfully enrolled!");
                window.location.href = "{{ route('students.index') }}";
            })
            .catch(() => {
                alert("Error saving biometric token.");
                this.disabled = false;
                this.innerHTML = '<i class="fa-solid fa-floppy-disk mr-2"></i> SAVE BIOMETRIC PROFILE';
            });
        });
    </script>
@endsection