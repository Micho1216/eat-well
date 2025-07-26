@extends('master')

@section('title', 'Login')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/login-register-verify.css') }}">
@endsection

@section('content')
    <div class="container-fluid mt-3">
        <div class="row content align-items-center justify-content-center ">
            <div class="col-12 col-sm-8 col-md-6 col-lg-6 col-xl-4 my-5">
                <div class="card text-bg-light rounded-5 d-block" id="login-card">
                    <div class="card-body p-5 p-sm-5 vh-75">
                        <div class="card-title text-center mb-3">OTP Verification</div>
                        <p class="card-text mb-5 text-center fs-5 fw-semibold">OTP have been sent to<br>{{$email}}.<br>Please check your email.</p>
                        <form method="POST" action="{{ route('auth.check') }}" novalidate>
                            @csrf
                            <input type="hidden" name="email" value="{{$email}}">
                            <div class="form-floating">
                                <input type="text" maxlength="6" inputmode="numeric" pattern="[0-9\s]*" name="otp" class="form-control m-0 @error('otp') is-invalid @enderror" id="otp" value="{{ old('otp') }}" placeholder="" >
                                <label for="otp" class="form-label m-0">One Time Password</label>
                                <div class="invalid-feedback my-2">{{ $errors->first('otp') }}</div>
                            </div>

                            
                            <button type="submit" class="mb-0 mt-5 w-100 gsi-material-button w-100">
                                <div class="gsi-material-button-state"></div>
                                <div class="gsi-material-button-content-wrapper">
                                    <span class="gsi-material-button-contents">Sign in</span>
                                </div>
                            </button>
                        </form>
                        
                        <form action="{{route('auth.resend-otp')}}" method="post" novalidate>
                            @csrf
                            <input type="hidden" name="email" value="{{$email}}">
                            <button type="submit" class="btn w-100">
                                <p class="text-success fs-6 text-center">Didn't receive the code? <u>Resend the code!</u></p>
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
@endsection
