@extends('components.vendor-nav')

@section('title')
    {{ __('manage-profile-vendor.title') }}
@endsection

@section('css')
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/manageProfilevendor.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
@endsection

@section('content')
    <div class="container container-custom">
        <div class="left-panel outer-panel">
            <div class="lexend font-medium manage-profile">
                <div class="left-panel-in photo-prof">
                    <img src="{{ asset('asset/vendorLogo/' . $vendor->logo) }}" alt="Profile Picture" class="prof-pict">
                </div>
                <div class="right-panel-in data-prof">
                    <p class="profile-name text-white lexend font-regular">{{ $user->name }}</p>
                    <p class="profile-status lexend font-bold">{{ $user->role }}
                    <p>
                    <p class="joined-date text-white lexend font-regular">
                        {{ __('manage-profile-vendor.joined_since') }} <span
                            class="date">{{ $user->created_at->format('d-m-Y') }}</span>
                    </p>
                </div>
            </div>
            <div class="menu ">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="menu-link active inter font-regular" id="profileTab"
                            href="#management-profile">{{ __('manage-profile-vendor.manage_profile') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="menu-link inter font-regular" id="securityTab"
                            href="#management-security">{{ __('manage-profile-vendor.manage_security') }}</a>
                    </li>
                </ul>
            </div>
            <ul class="nav flex-column sidebar-menu mobile-tabs">
                <li class="nav-item">
                    <a class="menu-link active inter font-regular" id="profileTab"
                        href="#management-profile">{{ __('manage-profile-vendor.manage_profile') }}</a>
                </li>
                <li class="nav-item">
                    <a class="menu-link inter font-regular" id="securityTab"
                        href="#management-security">{{ __('manage-profile-vendor.manage_security') }}</a>
                </li>
            </ul>
        </div>

        <div class="right-panel outer-panel">
            <div class="lexend font-medium outer-box scrollable-box">
                <div id="management-profile" class="management-section">
                    <div class="profile-manage">
                        <p class="lexend font-medium text-black title">{{ __('manage-profile-vendor.personal_profile') }}
                        </p>
                        <p class="inter font-regular text-black description">
                            {{ __('manage-profile-vendor.personal_profile_desc') }}</p>
                    </div>
                    <hr
                        style="height: 1.8px; background-color:black; opacity:100%; border: none; margin-left: 180px; margin-right: 180px;">
                    @session('success')
                        <p class="text-center" style="color: green">{{ session('success') }}</p>
                    @endsession
                    <div class="manage-profile-in">
                        <form action="{{ route('manage-profile-vendor.update') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PATCH')

                            <div class="datafoto">
                                <div class="data">
                                    <div class="alert alert-secondary" role="alert" style="width: 60%;">
                                        {{ __('manage-profile-vendor.email') }} : {{ $user->email }}
                                    </div>
                                    <label
                                        class="inter font-bold text-black data-title">{{ __('manage-profile-vendor.name') }}</label>
                                    <input type="text" class="lexend font-regular text-black name-input" id="nameInput"
                                        name="nameInput" value="{{ $vendor->name }}">
                                    @error('nameInput')
                                        <p style="color: red">{{ $message }}</p>
                                    @enderror

                                    <label
                                        class="inter font-bold text-black data-title">{{ __('manage-profile-vendor.telephone') }}</label>
                                    <input type="text" class="lexend font-regular text-black name-input" id="telpInput"
                                        name="telpInput" value="{{ $vendor->phone_number }}">
                                    @error('telpInput')
                                        <p style="color: red">{{ $message }}</p>
                                    @enderror

                                    <label class="inter font-bold text-black data-title"
                                        style="display: none">{{ __('manage-profile-vendor.dob') }}</label>
                                    <div class="dob-picker" style="display: none">
                                        <select class="dob-select font-regular" name="dob_month" id="dob_month">
                                            <option value="" selected></option>
                                            @for ($m = 1; $m <= 12; $m++)
                                                <option value="{{ $m }}">
                                                    {{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                                            @endfor
                                        </select>
                                        <select class="dob-select" name="dob_day" id="dob_day">
                                            <option value="" selected></option>
                                            @for ($d = 1; $d <= 31; $d++)
                                                <option value="{{ $d }}">
                                                    {{ str_pad($d, 2, '0', STR_PAD_LEFT) }}</option>
                                            @endfor
                                        </select>
                                        <select class="dob-select" name="dob_year" id="dob_year">
                                            <option value="" selected></option>
                                            @for ($y = date('Y'); $y >= 1900; $y--)
                                                <option value="{{ $y }}">{{ $y }}</option>
                                            @endfor
                                        </select>
                                    </div>

                                    <div class="time-row mt-3">
                                        <label class="time-label"
                                            for="breakfast_time_start">{{ __('manage-profile-vendor.breakfast_delivery') }}</label>
                                        <input type="time" class="time-input" name="breakfast_time_start"
                                            id="breakfast_time_start"
                                            value="{{ isset($breakfast_start) ? $breakfast_start : '' }}">
                                        <p class="mt-2">-</p>
                                        <input type="time" class="time-input" name="breakfast_time_end"
                                            id="breakfast_time_end"
                                            value="{{ isset($breakfast_end) ? $breakfast_end : '' }}">
                                    </div>

                                    <div class="time-row">
                                        <label class="time-label"
                                            for="lunch_time_start">{{ __('manage-profile-vendor.lunch_delivery') }}</label>
                                        <input type="time" class="time-input" name="lunch_time_start"
                                            id="lunch_time_start" value="{{ isset($lunch_start) ? $lunch_start : '' }}">
                                        <p class="mt-2">-</p>
                                        <input type="time" class="time-input" name="lunch_time_end"
                                            id="lunch_time_end" value="{{ isset($lunch_end) ? $lunch_end : '' }}">
                                    </div>

                                    <div class="time-row">
                                        <label class="time-label"
                                            for="dinner_time_start">{{ __('manage-profile-vendor.dinner_delivery') }}</label>
                                        <input type="time" class="time-input" name="dinner_time_start"
                                            id="dinner_time_start"
                                            value="{{ isset($dinner_start) ? $dinner_start : '' }}">
                                        <p class="mt-2">-</p>
                                        <input type="time" class="time-input" name="dinner_time_end"
                                            id="dinner_time_end" value="{{ isset($dinner_end) ? $dinner_end : '' }}">
                                    </div>
                                </div>

                                <div class="photo-data">
                                    <div class="profile-image-wrapper">
                                        <img src="{{ asset('asset/vendorLogo/' . $vendor->logo) }}" alt="Profile Picture"
                                            class="profile-picture" id="profilePicPreview">
                                        <label for="profilePicInput" class="change-image-label">
                                            <span
                                                class="material-symbols-outlined change-image-icon">add_photo_alternate</span>
                                            <input type="file" id="profilePicInput" name="profilePicInput"
                                                accept="image/*" style="display:none;" disabled>
                                        </label>
                                    </div>
                                    <div class="edit-btn-group d-flex flex-row p-2" style="width:100%">
                                        <button
                                            class="inter font-medium edit-data">{{ __('manage-profile-vendor.edit') }}</button>
                                    </div>

                                    <div class="modal fade" id="exampleModal" tabindex="-1"
                                        aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h1 class="modal-title fs-5" id="exampleModalLabel" style="color: rgb(46, 173, 46)">Data saved</h1>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Changes had been made !
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <hr class="section-divider">

                <div id="management-security" class="management-section mt-3">
                    <div class="security-manage">
                        <p class="lexend font-medium text-black title">
                            {{ __('manage-profile-vendor.security_management') }}</p>
                        <p class="inter font-regular text-black description">
                            {{ __('manage-profile-vendor.security_management_desc') }}</p>
                        <hr
                            style="height: 1.8px; background-color:black; opacity:100%; border: none; margin-left: 180px; margin-right: 180px;">
                    </div>
                    <div class="left-right-security">
                        <div class="left-security">
                            <p class="inter font-bold title-security">{{ __('manage-profile-vendor.mfa_management') }}</p>
                            <div class="mfa-warning">
                                @unless ($user->enabled_2fa)
                                    <span class="material-symbols-outlined mfa-warning-icon">warning</span>
                                    <span class="inter font-bold mfa-warning-text">
                                        {!! __('manage-profile-vendor.mfa_warning') !!}
                                    </span>
                                @endunless
                            </div>

                            <div class="mfa-toggle-row">
                                <form method="POST" action="{{ route('manage-two-factor') }}">
                                    @csrf
                                    <button type="submit" class="btn">
                                        <label class="mfa-switch">
                                            <input type="checkbox" @checked($user->enabled_2fa) />
                                            <span class="mfa-slider"></span>
                                        </label>
                                    </button>
                                </form>

                                <span class="inter font-bold mfa-toggle-label">
                                    @if ($user->enabled_2fa)
                                        {{ __('manage-profile-vendor.mfa_enable') }}
                                    @else
                                        Enable Multi Factor Authentication
                                    @endif
                                </span>
                            </div>
                            <p class="mfa-desc inter font-bold">
                                {{ __('manage-profile-vendor.mfa_desc') }}
                            </p>
                        </div>
                        <div class="security-divider"></div>
                        <div class="right-security">
                            <p class="inter font-bold title-security">{{ __('manage-profile-vendor.change_password') }}
                            </p>
                            <div class="change-password-form">
                                <div class="password-input-group">
                                    <input type="password" id="oldPassword" class="password-input"
                                        placeholder="{{ __('manage-profile-vendor.old_password') }}">
                                    <span class="toggle-password" data-target="oldPassword">
                                        <span class="material-symbols-outlined">visibility_off</span>
                                    </span>
                                </div>
                                <div class="password-input-group">
                                    <input type="password" id="newPassword" class="password-input"
                                        placeholder="{{ __('manage-profile-vendor.new_password') }}">
                                    <span class="toggle-password" data-target="newPassword">
                                        <span class="material-symbols-outlined">visibility_off</span>
                                    </span>
                                </div>
                                <div class="password-input-group">
                                    <input type="password" id="verifyPassword" class="password-input"
                                        placeholder="{{ __('manage-profile-vendor.verify_new_password') }}">
                                    <span class="toggle-password" data-target="verifyPassword">
                                        <span class="material-symbols-outlined">visibility_off</span>
                                    </span>
                                </div>
                                <div class="change-btn-group">
                                    <button
                                        class="inter save-password-btn">{{ __('manage-profile-vendor.change') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="logout">
        <form method="POST" action="{{ route('logout') }}" class="logout-form">
            @csrf
            <button type="submit" class="logout-btn inter font-regular">
                {{ __('manage-profile-vendor.logout') }}
            </button>
        </form>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/vendors/manageProfileVendor.js') }}"></script>
@endsection
