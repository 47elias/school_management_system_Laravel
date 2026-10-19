@extends('layouts.student')

@section('content')
<div class="space-y-4">
    <!-- PAGE TITLE -->
    <div class="flex items-center justify-between pb-2 border-b border-slate-300 dark:border-slate-800">
        <div>
            <h1 class="text-base font-black text-slate-900 dark:text-slate-100 tracking-tight">AI Academic Assistant</h1>
            <p class="text-xs font-bold text-slate-700 dark:text-slate-400">AI guidance for your academic and financial records</p>
        </div>
        <span class="px-2.5 py-1 text-[10px] font-black rounded bg-slate-900 text-white">
            {{ env('SCHOOL_ACRONYM', 'SMS') }} v{{ env('PORTAL_VERSION', '2.6') }}
        </span>
    </div>

    <!-- STATS ROW -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-white dark:bg-slate-900 rounded-lg p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center space-x-3">
            <div class="w-10 h-10 rounded-lg bg-slate-900 text-white flex items-center justify-center text-base shrink-0">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-[10px] font-black uppercase text-slate-700 dark:text-slate-400">Grade Level</p>
                <p class="text-sm font-black text-slate-900 dark:text-slate-100 truncate">{{ $student->grade }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-lg p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center space-x-3">
            <div class="w-10 h-10 rounded-lg {{ $calculatedBalance > 0 ? 'bg-rose-700 text-white' : 'bg-emerald-700 text-white' }} flex items-center justify-center text-base shrink-0">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-[10px] font-black uppercase text-slate-700 dark:text-slate-400">Fee Balance</p>
                <p class="text-sm font-black text-slate-900 dark:text-slate-100">{{ env('CURRENCY_SYMBOL', '$') }}{{ number_format($calculatedBalance, 2) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-lg p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center space-x-3">
            <div class="w-10 h-10 rounded-lg bg-amber-600 text-white flex items-center justify-center text-base shrink-0">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-[10px] font-black uppercase text-slate-700 dark:text-slate-400">Active Term</p>
                <p class="text-sm font-black text-slate-900 dark:text-slate-100 truncate">{{ $currentTerm->term_name ?? $student->term->name ?? 'N/A' }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-lg p-3.5 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center space-x-3">
            <div class="w-10 h-10 rounded-lg bg-emerald-700 text-white flex items-center justify-center text-base shrink-0">
                <i class="fa-solid fa-robot"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-[10px] font-black uppercase text-slate-700 dark:text-slate-400">Assistant Status</p>
                <p class="text-sm font-black text-slate-900 dark:text-slate-100">Ready</p>
            </div>
        </div>
    </div>

    <!-- CHAT BOX -->
    <div class="bg-white dark:bg-slate-900 rounded-lg border border-slate-300 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-300 dark:border-slate-800 flex items-center justify-between bg-slate-100 dark:bg-slate-950">
            <div class="flex items-center space-x-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                <h3 class="text-xs font-black text-slate-900 dark:text-slate-100">Interactive Assistant Chat</h3>
            </div>
            <button onclick="window.location.reload()" class="p-1 rounded text-slate-900 dark:text-slate-200 hover:bg-slate-200">
                <i class="fa-solid fa-rotate-right text-xs"></i>
            </button>
        </div>

        <div class="p-4">
            <div id="chat-window" class="h-[380px] overflow-y-auto space-y-3 pr-2 scroll-smooth">
                <!-- System Greeting -->
                <div class="flex items-start space-x-2.5 max-w-[85%]">
                    <div class="w-7 h-7 rounded bg-slate-900 text-white flex items-center justify-center text-xs shrink-0 mt-0.5">
                        <i class="fa-solid fa-robot"></i>
                    </div>
                    <div>
                        <div class="flex items-center space-x-2 mb-1">
                            <span class="text-[10px] font-black text-slate-900 dark:text-slate-100">System AI</span>
                            <span class="text-[10px] font-bold text-slate-700">{{ date('H:i') }}</span>
                        </div>
                        <div class="p-3 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs font-bold text-slate-900 dark:text-slate-100 leading-relaxed border border-slate-300 dark:border-slate-700">
                            Hello {{ $student->name }}! I have access to your financial and academic records. How can I assist you today?
                        </div>
                    </div>
                </div>
            </div>

            <div id="chat-loading" class="hidden my-2 text-xs font-bold text-slate-900 flex items-center space-x-2">
                <i class="fa-solid fa-spinner fa-spin text-slate-900"></i>
                <span>Assistant is thinking...</span>
            </div>
        </div>

        <div class="p-3 bg-slate-100 dark:bg-slate-950 border-t border-slate-300 dark:border-slate-800">
            <form id="chat-form" class="flex items-center space-x-2">
                <input type="text" 
                       id="user-input" 
                       name="message" 
                       placeholder="Ask about your fees, results, or school policies..." 
                       class="flex-1 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 rounded-lg px-3.5 py-2 text-xs font-bold text-slate-900 dark:text-slate-100 placeholder-slate-700 focus:outline-none"
                       autocomplete="off">
                <button type="submit" class="px-4 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-xs font-black flex items-center space-x-1.5 shrink-0">
                    <span>Send</span>
                    <i class="fa-solid fa-paper-plane text-[10px]"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('chat-form').addEventListener('submit', function(e) {
        e.preventDefault();

        const input = document.getElementById('user-input');
        const chatWindow = document.getElementById('chat-window');
        const loader = document.getElementById('chat-loading');
        const message = input.value.trim();

        if (!message) return;

        appendMessage('user', message);
        input.value = '';
        loader.classList.remove('hidden');
        chatWindow.scrollTop = chatWindow.scrollHeight;

        fetch("{{ route('student.ai_chat.message') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ message: message })
        })
        .then(response => response.json())
        .then(data => {
            loader.classList.add('hidden');
            appendMessage('bot', data.reply);
            chatWindow.scrollTop = chatWindow.scrollHeight;
        })
        .catch(error => {
            loader.classList.add('hidden');
            appendMessage('bot', "Error: System connection failed. Please try again.");
        });
    });

    function appendMessage(role, text) {
        const chatWindow = document.getElementById('chat-window');
        const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        const isBot = role === 'bot';
        const name = isBot ? 'System AI' : "{{ $student->name }}";
        const img = isBot ? "{{ asset('adminlte/dist/img/avatar5.png') }}" : "{{ asset($avatar) }}";

        let html = '';
        if (isBot) {
            html = `
                <div class="flex items-start space-x-2.5 max-w-[85%]">
                    <div class="w-7 h-7 rounded bg-slate-900 text-white flex items-center justify-center text-xs shrink-0 mt-0.5">
                        <i class="fa-solid fa-robot"></i>
                    </div>
                    <div>
                        <div class="flex items-center space-x-2 mb-1">
                            <span class="text-[10px] font-black text-slate-900 dark:text-slate-100">${name}</span>
                            <span class="text-[10px] font-bold text-slate-700">${time}</span>
                        </div>
                        <div class="p-3 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs font-bold text-slate-900 dark:text-slate-100 leading-relaxed border border-slate-300 dark:border-slate-700">
                            ${text}
                        </div>
                    </div>
                </div>
            `;
        } else {
            html = `
                <div class="flex items-start justify-end space-x-2.5 ml-auto max-w-[85%]">
                    <div class="text-right">
                        <div class="flex items-center justify-end space-x-2 mb-1">
                            <span class="text-[10px] font-bold text-slate-700">${time}</span>
                            <span class="text-[10px] font-black text-slate-900 dark:text-slate-100">${name}</span>
                        </div>
                        <div class="p-3 rounded-lg bg-slate-900 text-white text-xs font-bold leading-relaxed">
                            ${text}
                        </div>
                    </div>
                    <img src="${img}" class="w-7 h-7 rounded object-cover ring-2 ring-slate-900 shrink-0 mt-0.5" alt="User">
                </div>
            `;
        }

        chatWindow.insertAdjacentHTML('beforeend', html);
    }
</script>
@endsection