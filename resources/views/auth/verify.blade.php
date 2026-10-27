<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'SIT') }} | Reset Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-100 to-slate-300 flex items-center justify-center p-4">
    <main class="w-full max-w-md overflow-hidden rounded-2xl border-t-4 border-blue-600 bg-white p-8 shadow-2xl">
        <div class="mb-6 text-center">
            <p class="text-xs font-bold uppercase tracking-widest text-blue-600">Staff account recovery</p>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Reset your password</h1>
            <p class="mt-2 text-sm leading-6 text-slate-500">{{ $resetEmail ? 'Enter the code from your email and choose a new password.' : 'Enter your registered staff email to receive a verification code.' }}</p>
        </div>

        @if(session('status'))
            <p role="status" class="mb-5 rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800">{{ session('status') }}</p>
        @endif
        @if($errors->any())
            <ul role="alert" class="mb-5 list-inside list-disc rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        @endif

        @if($resetEmail)
            <p class="mb-5 break-words text-sm text-slate-600">Email: <strong>{{ $resetEmail }}</strong></p>
            <form method="POST" action="{{ route('password.update.final') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="otp" class="mb-1.5 block text-sm font-semibold text-slate-700">Email verification code</label>
                    <input id="otp" name="otp" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" minlength="6" maxlength="6" required autofocus placeholder="000000" class="block w-full rounded-lg border border-slate-300 px-3 py-3 text-center text-xl tracking-[0.4em] text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1.5 text-xs text-slate-500">Six digits. Valid for 10 minutes.</p>
                </div>
                <div>
                    <label for="password" class="mb-1.5 block text-sm font-semibold text-slate-700">New password</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" minlength="8" required placeholder="At least 8 characters" class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="password_confirmation" class="mb-1.5 block text-sm font-semibold text-slate-700">Confirm new password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                </div>
                <button type="submit" class="w-full rounded-lg bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Verify code and reset password</button>
            </form>
        @endif

        <form method="POST" action="{{ route('password.otp.send') }}" class="space-y-4 {{ $resetEmail ? 'mt-6 border-t border-slate-100 pt-5' : '' }}">
            @csrf
            <div>
                <label for="email" class="mb-1.5 block text-sm font-semibold text-slate-700">{{ $resetEmail ? 'Resend code or use another staff email' : 'Staff email address' }}</label>
                <input id="email" name="email" type="email" autocomplete="email" value="{{ old('email', $resetEmail) }}" required @if(!$resetEmail) autofocus @endif placeholder="you@school.com" class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="w-full rounded-lg px-4 py-3 text-sm font-semibold transition {{ $resetEmail ? 'bg-slate-100 text-blue-700 hover:bg-slate-200' : 'bg-blue-600 text-white hover:bg-blue-700' }}">{{ $resetEmail ? 'Send a new code' : 'Send verification code' }}</button>
        </form>
        <div class="mt-6 text-center"><a href="{{ route('login') }}" class="text-sm font-medium text-slate-500 hover:text-blue-600">Back to login</a></div>
    </main>
</body>
</html>
