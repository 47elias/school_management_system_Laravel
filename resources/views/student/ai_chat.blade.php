@extends('layouts.student')

@section('content')
<section class="content-header">
    <h1>
        AI Academic Assistant
        <small>{{ env('SCHOOL_ACRONYM') }} v{{ env('PORTAL_VERSION') }}</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">AI Chat</li>
    </ol>
</section>

<section class="content">
    {{-- TOP STATS ROW --}}
    <div class="row">
        {{-- GRADE BOX --}}
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box bg-aqua">
                <span class="info-box-icon"><i class="fa fa-graduation-cap"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Grade / Level</span>
                    <span class="info-box-number">{{ $student->grade }}</span>
                    <div class="progress"><div class="progress-bar" style="width: 100%"></div></div>
                    <span class="progress-description text-bold">{{ env('SCHOOL_NAME') }}</span>
                </div>
            </div>
        </div>

        {{-- FEE STATUS BOX --}}
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box {{ $calculatedBalance > 0 ? 'bg-red' : 'bg-green' }}">
                <span class="info-box-icon"><i class="fa fa-money"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Fee Balance</span>
                    <span class="info-box-number">{{ env('CURRENCY_SYMBOL') }}{{ number_format($calculatedBalance, 2) }}</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: {{ $paymentPercentage }}%"></div>
                    </div>
                    <span class="progress-description">
                        {{ number_format($paymentPercentage, 0) }}% Cleared
                    </span>
                </div>
            </div>
        </div>

        {{-- TERM BOX --}}
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box bg-yellow">
                <span class="info-box-icon"><i class="fa fa-calendar-check-o"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Active Term</span>
                    <span class="info-box-number">{{ $currentTerm->term_name ?? $student->term->name ?? 'N/A' }}</span>
                    <div class="progress"><div class="progress-bar" style="width: 100%"></div></div>
                    <span class="progress-description">Academic Year {{ date('Y') }}</span>
                </div>
            </div>
        </div>

        {{-- PERFORMANCE BOX --}}
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box bg-purple">
                <span class="info-box-icon"><i class="fa fa-line-chart"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Academic Status</span>
                    <span class="info-box-number">AI Enhanced</span>
                    <div class="progress"><div class="progress-bar" style="width: 100%"></div></div>
                    <span class="progress-description">Inspired to inspire</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            {{-- CHAT BOX --}}
            <div class="box box-primary direct-chat direct-chat-primary">
                <div class="box-header with-border">
                    <h3 class="box-title text-bold">Chat with Assistant</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-toggle="tooltip" title="Clear Chat" onclick="window.location.reload()">
                            <i class="fa fa-refresh"></i>
                        </button>
                    </div>
                </div>

                <div class="box-body">
                    <div class="direct-chat-messages" id="chat-window" style="height: 450px;">

                        <div class="direct-chat-msg">
                            <div class="direct-chat-info clearfix">
                                <span class="direct-chat-name pull-left">System AI</span>
                                <span class="direct-chat-timestamp pull-right">{{ date('H:i') }}</span>
                            </div>
                            <img class="direct-chat-img" src="{{ asset('adminlte/dist/img/avatar5.png') }}" alt="Bot Image">
                            <div class="direct-chat-text">
                                Hello {{ $student->name }}! I have access to your financial and academic records. How can I assist you today?
                            </div>
                        </div>

                    </div>

                    <div id="chat-loading" style="display: none; padding: 10px;">
                        <i class="fa fa-refresh fa-spin"></i> Assistant is thinking...
                    </div>
                </div>

                <div class="box-footer">
                    <form id="chat-form">
                        <div class="input-group">
                            <input type="text" id="user-input" name="message" placeholder="Ask about your fees, results, or school policies..." class="form-control" autocomplete="off">
                            <span class="input-group-btn">
                                <button type="submit" class="btn btn-primary btn-flat text-bold">Send Request</button>
                            </span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    document.getElementById('chat-form').addEventListener('submit', function(e) {
        e.preventDefault();

        const input = document.getElementById('user-input');
        const chatWindow = document.getElementById('chat-window');
        const loader = document.getElementById('chat-loading');
        const message = input.value.trim();

        if (!message) return;

        // 1. Append User Message to UI
        appendMessage('user', message);
        input.value = '';
        loader.style.display = 'block';
        chatWindow.scrollTop = chatWindow.scrollHeight;

        // 2. AJAX Request to Controller
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
            loader.style.display = 'none';
            appendMessage('bot', data.reply);
            chatWindow.scrollTop = chatWindow.scrollHeight;
        })
        .catch(error => {
            loader.style.display = 'none';
            appendMessage('bot', "Error: I'm having trouble connecting to the system. Please try again later.");
        });
    });

    function appendMessage(role, text) {
        const chatWindow = document.getElementById('chat-window');
        const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        const isBot = role === 'bot';
        const name = isBot ? 'System AI' : "{{ $student->name }}";
        const align = isBot ? 'left' : 'right';
        const pullName = isBot ? 'pull-left' : 'pull-right';
        const pullTime = isBot ? 'pull-right' : 'pull-left';
        const img = isBot ? "{{ asset('adminlte/dist/img/avatar5.png') }}" : "{{ asset($avatar) }}";
        const msgClass = isBot ? '' : 'right';

        const html = `
            <div class="direct-chat-msg ${msgClass}">
                <div class="direct-chat-info clearfix">
                    <span class="direct-chat-name ${pullName}">${name}</span>
                    <span class="direct-chat-timestamp ${pullTime}">${time}</span>
                </div>
                <img class="direct-chat-img" src="${img}" alt="User Image">
                <div class="direct-chat-text">
                    ${text}
                </div>
            </div>
        `;

        chatWindow.insertAdjacentHTML('beforeend', html);
    }
</script>

<style>
    .direct-chat-text { border-radius: 15px !important; }
    .direct-chat-primary .right > .direct-chat-text { background: #3c8dbc !important; border-color: #3c8dbc !important; }
    .info-box-number { font-size: 22px !important; }
    .text-bold { font-weight: 700; }
</style>
@endsection
