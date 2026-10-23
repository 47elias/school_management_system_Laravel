<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'School Portal') }} | Activate Student Account</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="min-h-screen bg-slate-100 px-4 py-10 text-slate-800">
    <main class="mx-auto w-full max-w-lg">
        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl shadow-slate-200/70">
            <div class="border-t-4 border-emerald-600 px-6 py-8 sm:px-9">
                <div class="mb-7 text-center">
                    <img src="{{ asset('images/school_logo.png') }}" class="mx-auto mb-4 h-16 w-auto object-contain" alt="{{ config('app.name', 'School') }} logo" onerror="this.hidden=true">
                    <p class="text-xs font-extrabold uppercase tracking-[.18em] text-emerald-700">Student Portal</p>
                    <h1 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900">Activate your account</h1>
                    <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-slate-500">Confirm your admission details, upload school fees proof, and choose a password. The school will create your student record and enable portal access after approving your payment.</p>
                </div>

                @if(session('status'))
                    <div class="mb-5 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800" role="status">{{ session('status') }}</div>
                @endif

                @if($errors->any())
                    <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" role="alert">
                        <p class="font-bold">We could not activate the account.</p>
                        <ul class="mt-1 list-inside list-disc">
                            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('student.activate.submit') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    <div>
                        <label for="identifier" class="mb-2 block text-sm font-bold text-slate-700">Admission Tracking ID or National ID / Birth Entry Number</label>
                        <input id="identifier" name="identifier" type="text" value="{{ old('identifier') }}" required autofocus autocomplete="off"
                            placeholder="Enter your tracking ID or ID number"
                            class="block w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">
                        <p class="mt-2 text-xs leading-5 text-slate-500">Use the tracking ID from your admission confirmation, or the National ID / Birth Entry Number on your application.</p>
                    </div>

                    <div>
                        <label for="payment_proof" class="mb-2 block text-sm font-bold text-slate-700">School fees proof of payment</label>
                        <input id="payment_proof" name="payment_proof" type="file" accept=".pdf,.jpg,.jpeg,.png" required
                            class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-3 file:py-2 file:text-xs file:font-bold file:text-emerald-700">
                        <p class="mt-2 text-xs leading-5 text-slate-500">Upload a PDF or image, up to 5 MB. The school must approve it before you can sign in.</p>
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-sm font-bold text-slate-700">Create password</label>
                        <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password" placeholder="At least 8 characters"
                            class="block w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">
                    </div>

                    <div>
                        <label for="password_confirmation" class="mb-2 block text-sm font-bold text-slate-700">Confirm password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password" placeholder="Enter the password again"
                            class="block w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">
                    </div>

                    <button type="submit" class="flex w-full items-center justify-center rounded-xl bg-emerald-600 px-5 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-emerald-600/20 transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-500/20">
                        Activate portal account <span class="ml-2" aria-hidden="true">→</span>
                    </button>
                </form>

                <div class="mt-6 border-t border-slate-100 pt-5 text-center">
                    <a href="{{ route('student.login') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">Back to student login</a>
                </div>
            </div>
        </section>
        <p class="mt-6 text-center text-xs text-slate-500">Activation is available to students with approved admissions or active school records.</p>
    </main>
</body>
</html>
