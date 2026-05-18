<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Password Reset | {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center p-4">
    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-md border-t-4 border-green-600">
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800 uppercase">Reset Password</h2>
            <p class="text-gray-500 text-sm">Verify your student details to proceed</p>
        </div>

        @if(session('success'))
            <div id="success-alert" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm flex items-center">
                <i class="fa fa-check-circle fa-lg mr-3"></i>
                <div>
                    <p class="font-bold">Success!</p>
                    <p class="text-sm">{{ session('success') }}</p>
                </div>
            </div>

            <script>
                // Wait 3 seconds then redirect to login
                setTimeout(function() {
                    window.location.href = "{{ route('student.login') }}";
                }, 3000);
            </script>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-3 mb-4 text-sm flex items-center">
                <i class="fa fa-warning mr-2"></i>
                {{ session('error') }}
            </div>
        @endif

        @if(!session('success'))
        <form action="{{ route('student.password.update') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Student Number</label>
                <input type="text" name="student_number" value="{{ old('student_number') }}"
                       class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-green-500 outline-none"
                       placeholder="e.g. {{ env('SCHOOL_ACRONYM') }}260001" required autofocus>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Date of Birth</label>
                <input type="date" name="dob" value="{{ old('dob') }}"
                       class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-green-500 outline-none"
                       required>
            </div>

            <div class="border-t border-gray-100 my-4"></div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">New Password</label>
                <input type="password" name="password"
                       class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-green-500 outline-none"
                       placeholder="Min. 8 characters" required>
                @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Confirm New Password</label>
                <input type="password" name="password_confirmation"
                       class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-green-500 outline-none"
                       placeholder="Repeat password" required>
            </div>

            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-md transition duration-200">
                UPDATE PASSWORD <i class="fa fa-save ml-2"></i>
            </button>
        </form>
        @endif

        <div class="mt-6 text-center">
            <a href="{{ route('student.login') }}" class="text-sm text-green-600 hover:underline">
                <i class="fa fa-arrow-left"></i> Back to Student Login
            </a>
        </div>
    </div>
</body>
</html>
