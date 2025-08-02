@extends('master')

@section('title', 'Forgotten Password')

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
                    <div class="card-body p-5 p-sm-5">
                        <div class="h5 card-title text-center mt-5 mb-5">{{ __('auth/forgot-password.title') }}</div>
                        <form method="post" action="{{route('password.request') }}" novalidate>
                            @csrf
                            <div class="form-floating mb-3">
                                <input type="email" name="email" class="form-control m-0 @error('email') is-invalid @enderror @if(session('status')) is-valid @endif" id="email" value="{{ old('email') }}" placeholder="" >
                                <label for="email" class="form-label m-0">{{ __('auth/forgot-password.email') }}</label>
                                <div class="invalid-feedback">{{ $errors->first('email') }}</div>
                                <div class="valid-feedback">{{ session('status') }}</div>
                            </div>

                            <button type="submit" class="mb-2 mt-5 w-100 gsi-material-button">
                                <div class="gsi-material-button-state"></div>
                                <div class="gsi-material-button-content-wrapper">
                                    <span class="gsi-material-button-contents">{{ __('auth/forgot-password.continue') }}</span>
                                </div>
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
@endsection
