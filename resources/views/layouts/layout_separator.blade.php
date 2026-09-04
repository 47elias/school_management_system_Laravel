    @include('layouts.topbar')
    
    {{-- DYNAMIC SIDEBAR: Loads the correct sidebar based on user role --}}
    @if(auth()->check() && auth()->user()->role === 'admin')
        @include('layouts.sidebar') {{-- Assuming this is your admin sidebar file --}}
    @elseif(auth()->check() && auth()->user()->role === 'receptionist')
        @include('layouts.receptionist_sidebar') {{-- Assuming this is your receptionist sidebar file --}}
    @else
        @include('layouts.sidebar') {{-- Fallback for any other user --}}
    @endif