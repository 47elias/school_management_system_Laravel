<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Teacher Portal | {{ env('SCHOOL_ACRONYM') }}</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    {{-- Reusing your existing AdminLTE CSS components --}}
    @include('components.adminlte')
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

    {{-- Top Header --}}
    @include('layouts.topbar')

    {{-- Left side column (The new Teacher Sidebar) --}}
    @include('layouts.teacher_sidebar')

    {{-- Content Wrapper --}}
    <div class="content-wrapper">
        @yield('content')
    </div>

    {{-- Footer --}}
    @include('layouts.footer')

</div>

{{-- Reusing your existing AdminLTE Scripts --}}
@include('components.scripts')
@yield('scripts')

</body>
</html>
