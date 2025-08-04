@extends('master')

@section('title', 'Login')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/login-register-verify.css') }}">
@endsection

@section('content')
    <div id="translation-data"
        data-timer-text="{{ __('auth/verify-otp.timer') }}"
        data-minute-text="{{ __('auth/verify-otp.minute') }}"
        data-second-text="{{ __('auth/verify-otp.second') }}"
        data-expired-text="{{ __('auth/verify-otp.expired') }}">
    </div>

    <div id="time-data"
        data-minutes-text="{{ $minutes }}"
        data-seconds-text="{{ $seconds }}">
    </div>

    <div class="container-fluid mt-3">
        <div class="row content align-items-center justify-content-center ">
            <div class="col-12 col-sm-8 col-md-6 col-lg-6 col-xl-4 my-5">
                <div class="card text-bg-light rounded-5 d-block" id="login-card">
                    <div class="card-body p-5 p-sm-5 vh-75">
                        <form action="{{ route('logout') }}" method="post">
                            @csrf
                            <button type="submit" class="btn btn-default">
                                <img src="{{ asset('asset/password/arrow_back_45dp_E3E3E3_FILL0_wght400_GRAD0_opsz48.png') }}" alt="">
                            </button>
                        </form>
                        <div class="card-title text-center mb-3">{{ __('auth/verify-otp.title') }}</div>
                        <p class="card-text text-center fs-5 fw-semibold">{{ __('auth/verify-otp.desc', ['email' => $email]) }}</p>
                        <p class="text-success mb-5 fs-6 text-center" id="timer"></p>
                        <form method="POST" action="{{ route('auth.check') }}" novalidate>
                            @csrf
                            <input type="hidden" name="email" value="{{$email}}">
                            <div class="form-floating">
                                <input type="text" maxlength="6" inputmode="numeric" pattern="[0-9\s]*" name="otp" class="form-control m-0 @error('otp') is-invalid @enderror" id="otp" value="{{ old('otp') }}" placeholder="" >
                                <label for="otp" class="form-label m-0">{{ __('auth/verify-otp.otp') }}</label>
                                <div class="invalid-feedback my-2">{{ $errors->first('otp') }}</div>
                            </div>

                            <button type="submit" class="mb-0 mt-5 w-100 gsi-material-button w-100">
                                <div class="gsi-material-button-state"></div>
                                <div class="gsi-material-button-content-wrapper">
                                    <span class="gsi-material-button-contents">{{ __('auth/verify-otp.sign_in') }}</span>
                                </div>
                            </button>
                        </form>
                        
                        <form action="{{route('auth.resend-otp')}}" method="post" novalidate>
                            @csrf
                            <input type="hidden" name="email" value="{{$email}}">
                            <button type="submit" class="btn w-100" id="resend">
                                <p class="text-success fs-6 text-center">{{ __('auth/verify-otp.resend') }}</p>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="fruits-img d-none d-md-block col-md-6 col-xl-7">
                <img src="{{ asset('asset/login-page/login-fruits.png') }}" class="img-fluid" alt="">
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
    <script src="{{asset('js/login-register.js') }}"></script>
    <script src="{{asset('js/auth/verify-otp.js') }}"></script>
@endsection
