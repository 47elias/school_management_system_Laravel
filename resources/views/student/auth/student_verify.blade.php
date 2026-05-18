<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Reset | {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 h-screen flex items-center justify-center p-4">
    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md border-t-8 border-blue-600">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-black text-slate-800 tracking-tight">STUDENT RESET</h2>
            <p class="text-slate-500 text-sm">Enter your details to change your password</p>
        </div>

        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-3 mb-6 text-sm">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('student.password.update') }}" method="POST" class="space-y-5">
            @csrf

            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Student Number</label>
                    <input type="text" name="student_number" value="{{ old('student_number') }}"
                           class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition"
                           placeholder="e.g. S2026001" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Date of Birth</label>
                    <input type="date" name="dob" value="{{ old('dob') }}"
                           class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition"
                           required>
                </div>
            </div>

            <hr class="border-slate-100">

            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">New Password</label>
                    <input type="password" name="password"
                           class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition"
                           placeholder="••••••••" required>
                    @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Confirm Password</label>
                    <input type="password" name="password_confirmation"
                           class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition"
                           placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg shadow-md hover:shadow-lg transition-all transform active:scale-[0.98]">
                RESET MY PASSWORD
            </button>
        </form>

        <div class="mt-8 text-center border-t pt-4">
            <a href="{{ route('student.login') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800">
                &larr; Return to Student Login
            </a>
        </div>
    </div>
</body>
</html>
