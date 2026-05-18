@extends('layouts.teacher_app')

@section('content')
<section class="content-header">
    <h1 class="font-bold text-gray-800">
        <i class="fa fa-user-circle text-blue mr-2"></i> My Profile
        <small>Account Management</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('teacher.dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Profile</li>
    </ol>
</section>

<section class="content">
    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible shadow-sm">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="icon fa fa-check"></i> {{ session('success') }}
        </div>
    @endif

    <div class="row">
        {{-- Left Column: User Info Card --}}
        <div class="col-md-4">
            <div class="box box-primary border-t-4 shadow-md">
                <div class="box-body box-profile">
                    <div class="flex justify-center py-6">
                        <div class="relative">
                            <img class="profile-user-img img-responsive img-circle border-4 border-gray-200 p-1"
                                 src="{{ asset('adminlte/dist/img/avatar5.png') }}"
                                 alt="User profile picture"
                                 style="width: 110px; height: 110px; object-fit: cover;">
                            <span class="absolute bottom-1 right-1 bg-green-500 border-2 border-white w-5 h-5 rounded-full shadow-sm" title="Active Account"></span>
                        </div>
                    </div>

                    <h3 class="profile-username text-center font-bold text-2xl mb-1 text-gray-800">{{ $user->name }}</h3>
                    <p class="text-muted text-center uppercase tracking-tighter font-semibold text-xs bg-gray-100 py-1 px-3 rounded-full inline-block mx-auto flex justify-center w-max">
                        {{ $user->role }}
                    </p>

                    <div class="mt-8 space-y-1">
                        <ul class="list-group list-group-unbordered px-2">
                            <li class="list-group-item border-t-0 flex justify-between py-3">
                                <span class="text-gray-500 font-medium"><i class="fa fa-id-card-o mr-2 text-blue"></i> National ID</span>
                                <span class="text-gray-800 font-bold">{{ $user->national_id }}</span>
                            </li>
                            <li class="list-group-item flex justify-between py-3">
                                <span class="text-gray-500 font-medium"><i class="fa fa-barcode mr-2 text-blue"></i> EC Number</span>
                                <span class="text-gray-800 font-bold">{{ $user->ec_number }}</span>
                            </li>
                            <li class="list-group-item border-b-0 flex justify-between py-3">
                                <span class="text-gray-500 font-medium"><i class="fa fa-calendar-check-o mr-2 text-blue"></i> Joined</span>
                                <span class="text-gray-800 font-bold">{{ $user->created_at->format('d M Y') }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="box-footer bg-gray-50 text-center py-4 border-t border-gray-100">
                    <p class="text-xs text-gray-400 m-0">Account status verified by Admin</p>
                </div>
            </div>
        </div>

        {{-- Right Column: Form --}}
        <div class="col-md-8">
            <div class="nav-tabs-custom shadow-md rounded-lg overflow-hidden border">
                <ul class="nav nav-tabs bg-gray-50 border-b">
                    <li class="active">
                        <a href="#settings" data-toggle="tab" class="font-bold py-4 px-6">
                            <i class="fa fa-sliders text-blue mr-2"></i> Update Settings
                        </a>
                    </li>
                </ul>

                <div class="tab-content p-8">
                    <div class="active tab-pane" id="settings">
                        <form class="form-horizontal" method="POST" action="{{ route('teacher.profile.update') }}">
                            @csrf

                            {{-- Section: Personal --}}
                            <div class="flex items-center mb-6">
                                <span class="text-sm font-bold uppercase tracking-widest text-blue-600 mr-4">Identity</span>
                                <div class="flex-grow border-t border-gray-100"></div>
                            </div>

                            <div class="form-group mb-6">
                                <label class="col-sm-3 control-label text-gray-700">Full Name</label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <span class="input-group-addon bg-gray-50"><i class="fa fa-lock text-gray-400"></i></span>
                                        <input type="text" class="form-control btn-flat h-10 bg-gray-50 cursor-not-allowed font-medium text-gray-600 shadow-none border-gray-200"
                                               value="{{ $user->name }}" readonly>
                                    </div>
                                    <input type="hidden" name="name" value="{{ $user->name }}">
                                    <p class="mt-2 text-xs text-gray-400 italic">Legal names are locked and managed by the administration.</p>
                                </div>
                            </div>

                            <div class="form-group mb-6 @error('email') has-error @enderror">
                                <label class="col-sm-3 control-label text-gray-700">Email Address</label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <span class="input-group-addon bg-gray-50"><i class="fa fa-envelope-o text-blue"></i></span>
                                        <input type="email" name="email" class="form-control btn-flat h-10 border-gray-200" value="{{ old('email', $user->email) }}" required>
                                    </div>
                                    @error('email') <span class="help-block text-red-500 text-xs font-semibold">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="form-group mb-10 @error('phone_number') has-error @enderror">
                                <label class="col-sm-3 control-label text-gray-700">Phone Number</label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <span class="input-group-addon bg-gray-50"><i class="fa fa-phone text-blue"></i></span>
                                        <input type="text" name="phone_number" class="form-control btn-flat h-10 border-gray-200" value="{{ old('phone_number', $user->phone_number) }}" placeholder="+263 ...">
                                    </div>
                                    @error('phone_number') <span class="help-block text-red-500 text-xs font-semibold">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            {{-- Section: Security --}}
                            <div class="flex items-center mb-6">
                                <span class="text-sm font-bold uppercase tracking-widest text-red-600 mr-4">Security</span>
                                <div class="flex-grow border-t border-gray-100"></div>
                            </div>

                            <div class="form-group mb-6 @error('password') has-error @enderror">
                                <label class="col-sm-3 control-label text-gray-700">New Password</label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <span class="input-group-addon bg-gray-50"><i class="fa fa-key text-gray-400"></i></span>
                                        <input type="password" name="password" class="form-control btn-flat h-10 border-gray-200" placeholder="••••••••">
                                    </div>
                                    <p class="mt-2 text-xs text-gray-400">Leave blank to keep your current password.</p>
                                    @error('password') <span class="help-block text-red-500 text-xs font-semibold">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="form-group mb-10">
                                <label class="col-sm-3 control-label text-gray-700">Verify Password</label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <span class="input-group-addon bg-gray-50"><i class="fa fa-check-circle-o text-gray-400"></i></span>
                                        <input type="password" name="password_confirmation" class="form-control btn-flat h-10 border-gray-200" placeholder="••••••••">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group no-margin">
                                <div class="col-sm-offset-3 col-sm-9">
                                    <button type="submit" class="btn btn-primary btn-flat px-8 py-2 font-bold shadow transition-all hover:bg-blue-700 uppercase tracking-wider text-xs">
                                        <i class="fa fa-save mr-2"></i> Save Changes
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Support Callout --}}
            <div class="callout border-l-4 border-blue-500 bg-white p-6 mt-6 shadow-sm flex items-start">
                <div class="mr-4 text-blue-500 mt-1">
                    <i class="fa fa-info-circle fa-2x"></i>
                </div>
                <div>
                    <h4 class="font-bold text-gray-800 m-0 mb-1">Need help with profile data?</h4>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        Data such as <strong>Full Name, National ID, and EC Number</strong> are verified records.
                        For corrections, please contact the administration or
                        <a href="https://www.elias.co.zw" target="_blank" class="text-blue-600 font-bold hover:underline decoration-blue-200">{{ env('SCHOOL_NAME') }} IT Support</a>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
