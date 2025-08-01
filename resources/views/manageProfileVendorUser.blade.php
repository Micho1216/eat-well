@extends('components.vendor-nav')

@section('title', 'Manage Prodile')
@section('css')
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/manageProfile.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous">
    </script>
@endsection

@section('content')
    <div class="container container-custom">
        <div class="left-panel outer-panel">
            <div class="lexend font-medium manage-profile">
                <div class="left-panel-in photo-prof">
                    <img src="{{ asset('asset/profile/' . $user->profilePath) }}" alt="Profile Picture" class="prof-pict">
                </div>
                <div class="right-panel-in data-prof">
                    <p class="profile-name text-white lexend font-regular">{{ $user->name }}</p>
                    <p class="profile-status lexend font-bold">{{ $user->role }}
                    <p>
                    <p class="joined-date text-white lexend font-regular">
                        {{ __('manage-profile.joined_since') }}: <span
                            class="date">{{ $user->created_at->format('d-m-Y') }}</span>
                    </p>
                </div>
            </div>
            <div class="menu ">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="menu-link active inter font-regular" id="profileTab"
                            href="#management-profile">{{ __('manage-profile.manage_profile') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="menu-link inter font-regular" id="securityTab"
                            href="#management-security">{{ __('manage-profile.manage_security') }}</a>
                    </li>
                    @if (Auth::user()->role->value == 'Customer')
                        <li class="nav-item">
                            <a class="menu-link inter font-regular"
                                href="/manage-address">{{ __('manage-profile.manage_address') }}</a>
                        </li>
                    @elseif (Auth::user()->role->value == 'Vendor')
                        <li class="nav-item">
                            <a class="menu-link inter font-regular"
                                href="/manage-profile-vendor">{{ __('manage-profile.edit_catering') }}</a>
                        </li>
                    @endif


                </ul>
            </div>
            <ul class="nav flex-column sidebar-menu mobile-tabs">
                <li class="nav-item">
                    <a class="menu-link active inter font-regular" id="profileTab"
                        href="#management-profile">{{ __('manage-profile.manage_profile') }}</a>
                </li>
                <li class="nav-item">
                    <a class="menu-link inter font-regular" id="securityTab"
                        href="#management-security">{{ __('manage-profile.manage_security') }}</a>
                </li>
                @if (Auth::user()->role->value == 'Customer')
                    <li class="nav-item">
                        <a class="menu-link inter font-regular"
                            href="/manage-address">{{ __('manage-profile.manage_address') }}</a>
                    </li>
                @elseif (Auth::user()->role->value == 'Vendor')
                    <li class="nav-item">
                        <a class="menu-link inter font-regular"
                            href="/manage-profile-vendor">{{ __('manage-profile.edit_catering') }}</a>
                    </li>
                @endif
            </ul>

        </div>


        <div class="right-panel outer-panel">
            <div class="lexend font-medium outer-box scrollable-box">
                <div id="management-profile" class="management-section">
                    <div class="profile-manage">
                        <p class="lexend font-medium text-black title">{{ __('manage-profile.personal_profile') }}</p>
                        <p class="inter font-regular text-black description">{{ __('manage-profile.subtitle') }}</p>
                    </div>
                    <hr
                        style="height: 1.8px; background-color:black; opacity:100%; border: none; margin-left: 180px; margin-right: 180px;">



                    @if (session('success'))
                        @if (session('success'))
                            <div class="position-fixed top-0 end-0 p-3" style="z-index: 1100">
                                <div id="successToast" class="toast align-items-center text-bg-success border-0"
                                    role="alert" aria-live="assertive" aria-atomic="true">
                                    <div class="d-flex">
                                        <div class="toast-body">
                                            {{ session('success') }}
                                        </div>
                                        <button type="button" class="btn-close btn-close-white me-2 m-auto"
                                            data-bs-dismiss="toast" aria-label="Close"></button>
                                    </div>
                                </div>
                            </div>

                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const toastEl = document.getElementById('successToast');
                                    const toast = new bootstrap.Toast(toastEl, {
                                        autohide: true,
                                        delay: 3000 // 3 detik
                                    });
                                    toast.show();
                                });
                            </script>
                        @endif
                    @endif
                    <div class="manage-profile-in">
                        <form action="{{ route('manage-profile-vendor-account.updateUser') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PATCH')
                            <div class="datafoto">
                                <div class="data">
                                    <label
                                        class="inter font-bold text-black data-title">{{ __('manage-profile.name') }}</label>
                                    <input type="text" class="lexend font-regular text-black name-input" id="nameInput"
                                        name="nameInput" value="{{ $user->name }}">

                                    @error('nameInput')
                                        <div class="" style="color: rgb(194, 12, 12)">{{ $message }}</div>
                                    @enderror

                                    <label class="inter font-bold text-black data-title"
                                        style="display: none">{{ __('manage-profile.date_of_birth') }}</label>
                                    <div class="dob-picker" style="display: none">
                                        <select class="dob-select font-regular" name="dob_month" id="dob_month">
                                            @if (empty($user->dateOfBirth))
                                                <option value="" selected>mm</option>
                                            @else
                                                <option value="" selected>{{ $user->dateOfBirth->format('m') }}
                                                </option>
                                            @endif

                                            @for ($m = 1; $m <= 12; $m++)
                                                <option value="{{ $m }}">
                                                    {{ str_pad($m, 2, '0', STR_PAD_LEFT) }}
                                                </option>
                                            @endfor
                                        </select>
                                        <select class="dob-select" name="dob_day" id="dob_day">
                                            @if (empty($user->dateOfBirth))
                                                <option value="" selected>dd</option>
                                            @else
                                                <option value="" selected>{{ $user->dateOfBirth->format('d') }}
                                                </option>
                                            @endif
                                            @for ($d = 1; $d <= 31; $d++)
                                                <option value="{{ $d }}">
                                                    {{ str_pad($d, 2, '0', STR_PAD_LEFT) }}
                                                </option>
                                            @endfor
                                        </select>
                                        <select class="dob-select" name="dob_year" id="dob_year">
                                            @if (empty($user->dateOfBirth))
                                                <option value="" selected>YYYY</option>
                                            @else
                                                <option value="" selected>{{ $user->dateOfBirth->format('Y') }}
                                                </option>
                                            @endif
                                            @for ($y = date('Y'); $y >= 1900; $y--)
                                                <option value="{{ $y }}">{{ $y }}</option>
                                            @endfor
                                        </select>
                                    </div>

                                    <label for="dateOfBirth"
                                        class="inter font-bold text-black data-title">{{ __('manage-profile.date_of_birth') }}</label>
                                    <div class="dob-picker" disa>
                                        <input type="date" class="dob-select font-regular" name="dateOfBirth"
                                            id="dateOfBirth"
                                            value="{{ old('dateOfBirth', optional($user->dateOfBirth)->format('Y-m-d')) }}"
                                            disabled>

                                    </div>
                                    @error('dateOfBirth')
                                        <div class="" style="color: rgb(194, 12, 12)">{{ $message }}</div>
                                    @enderror



                                    <p class="inter font-bold text-black data-title gender">
                                        {{ __('manage-profile.gender') }}</p>
                                    <div class="gender-group">
                                        @if ($user->genderMale == 1)
                                            <input type="radio" id="male" name="gender" value="male"
                                                class="gender-radio" checked>
                                            <label for="male"
                                                class="gender-label">{{ __('manage-profile.male') }}</label>
                                            <input type="radio" id="female" name="gender" value="female"
                                                class="gender-radio">
                                            <label for="female"
                                                class="gender-label lexend font-medium text-black">{{ __('manage-profile.female') }}</label>
                                        @else
                                            <input type="radio" id="male" name="gender" value="male"
                                                class="gender-radio">
                                            <label for="male"
                                                class="gender-label">{{ __('manage-profile.male') }}</label>
                                            <input type="radio" id="female" name="gender" value="female"
                                                class="gender-radio" checked>
                                            <label for="female"
                                                class="gender-label lexend font-medium text-black">{{ __('manage-profile.female') }}</label>
                                        @endif

                                    </div>


                                    @error('gender')
                                        <div class="" style="color: rgb(194, 12, 12)">{{ $message }}</div>
                                    @enderror

                                    <div class="alert alert-secondary mt-3" role="alert">
                                        Email : {{ $user->email }}
                                    </div>
                                </div>
                                <div class="photo-data">
                                    <div class="profile-image-wrapper">
                                        <img src="{{ $user->profilePath ? asset('asset/profile/' . basename($user->profilePath)) : asset('profile/no-profile.png') }}"
                                            alt="Profile Picture" class="profile-picture" id="profilePicPreview">

                                        <label for="profilePicInput" class="change-image-label">
                                            <span class="material-symbols-outlined change-image-icon">
                                                add_photo_alternate
                                            </span>
                                            <input type="file" id="profilePicInput" name="profilePicInput"
                                                accept="image/*" style="display: none;" disabled>
                                        </label>
                                    </div>

                                    <div class="edit-btn-group d-flex flex-row">
                                        <button class="inter font-medium edit-data">Edit</button>
                                    </div>
                                    <p class="text-center" style="color: rgb(242, 185, 12)">
                                        {{ __('manage-profile.click_edit') }}</p>


                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <hr class="section-divider">
                <div id="management-security" class="management-section mt-4">

                    <div class="security-manage">
                        <p class="lexend font-medium text-black title">{{ __('manage-profile.security_management') }}</p>
                        <p class="inter font-regular text-black description">{{ __('manage-profile.subtitle') }}</p>
                        <hr
                            style="height: 1.8px; background-color:black; opacity:100%; border: none; margin-left: 180px; margin-right: 180px;">
                    </div>
                    <div class="left-right-security">
                        <div class="left-security">
                            <p class="inter font-bold title-security">{{ __('manage-profile.mfa_management') }}</p>
                            <div class="mfa-warning">
                                <span class="material-symbols-outlined mfa-warning-icon">warning</span>
                                <span class="inter font-bold mfa-warning-text">
                                    {{ __('manage-profile.mfa_warning') }}
                                </span>
                            </div>

                            <div class="mfa-toggle-row">
                                <label class="mfa-switch">
                                    <input type="checkbox" id="mfaToggle">
                                    <span class="mfa-slider"></span>
                                </label>
                                <span
                                    class="inter font-bold mfa-toggle-label">{{ __('manage-profile.enable_mfa') }}</span>
                            </div>

                            <p class="mfa-desc inter font-bold">
                                {{ __('manage-profile.mfa_description') }}
                            </p>
                        </div>
                        <div class="security-divider"></div>
                        <div class="right-security">
                            <p class="inter font-bold title-security">{{ __('manage-profile.change_password') }}</p>
                            <div class="change-password-form">
                                <div class="password-input-group">
                                    <input type="password" id="oldPassword" class="password-input"
                                        placeholder="{{ __('manage-profile.old_password') }}">
                                    <span class="toggle-password" data-target="oldPassword">
                                        <span class="material-symbols-outlined">visibility_off</span>
                                    </span>
                                </div>
                                <div class="password-input-group">
                                    <input type="password" id="newPassword" class="password-input"
                                        placeholder="{{ __('manage-profile.new_password') }}">
                                    <span class="toggle-password" data-target="newPassword">
                                        <span class="material-symbols-outlined">visibility_off</span>
                                    </span>
                                </div>
                                <div class="password-input-group">
                                    <input type="password" id="verifyPassword" class="password-input"
                                        placeholder="{{ __('manage-profile.verify_password') }}">
                                    <span class="toggle-password" data-target="verifyPassword">
                                        <span class="material-symbols-outlined">visibility_off</span>
                                    </span>
                                </div>
                                <div class="change-btn-group">
                                    <button class="inter save-password-btn">{{ __('manage-profile.change') }}</button>
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
                {{ __('manage-profile.logout') }}
            </button>
        </form>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/customer/manageProfile.js') }}"></script>
@endsection
