@extends('master')

@section('title', 'Customer Registration')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/login-register-verify.css') }}">
@endsection

@section('content')
    <div class="container-fluid mt-5">
        <div class="row content align-items-center justify-content-center ">
            <div class="col-12 col-sm-8 col-md-6 col-lg-6 col-xl-4 my-5">
                <div class="card text-bg-light rounded-5 d-block" id="login-card">
                    <div class="card-body p-5 p-sm-5">
                        <div class="h5 card-title text-center mb-5">Customer Registration</div>
                        <form action="{{ route('register', ['role' => 'customer']) }}" method="post" novalidate>
                            @csrf

                            <div class="form-floating mb-3">
                                <input type="text" name="name" id="name" class="form-control m-0 @error('name') is-invalid @enderror" value="{{old('name')}}" placeholder="">
                                <label for="name" class="form-label m-0">Name</label>
                                <div class="invalid-feedback">@error('name') {{$errors->first('name')}}@enderror</div>
                            </div>

                            <div class="form-floating mb-3">
                                <input type="email" name="email" id="email" class="form-control m-0 @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="">
                                <label for="email" class="form-label m-0">Email</label>
                                <div class="invalid-feedback">@error('email') {{$errors->first('email')}} @enderror</div>
                            </div>

                            <div class="form-floating mb-3">
                                <input type="password" name="password" id="password" class="form-control m-o @error('password') is-invalid @enderror" placeholder="">
                                <label for="password" class="form-label m-0">Password</label>
                                <div class="invalid-feedback">@error('password') {{$errors->first('password')}}@enderror</div>
                            </div>


                            <div class="form-floating mb-3">
                                <input type="password" name="password_confirmation" id="pasword_confirmation" class="form-control m-0 @error('password_confirmation') is-invalid @enderror" placeholder="">
                                <label for="password_confirmation" class="form-label m-0">Password Confirmation</label>
                                <div class="invalid-feedback">@error('password_confirmation') {{$errors->first('password_confirmation')}}@enderror</div>
                            </div>

                            <button type="submit" class="mb-2 mt-4 w-100 gsi-material-button w-100">
                                <div class="gsi-material-button-state"></div>
                                <div class="gsi-material-button-content-wrapper">
                                    <span class="gsi-material-button-contents">Register</span>
                                </div>
                            </button>
                        </form>

                        <a href="{{ route('auth.redirect', ['provider'=>'google', 'role' => 'customer']) }}">
                            <button class="gsi-material-button w-100">
                                <div class="gsi-material-button-state"></div>
                                <div class="gsi-material-button-content-wrapper">
                                    <div class="gsi-material-button-icon">
                                        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" xmlns:xlink="http://www.w3.org/1999/xlink" style="display: block;">
                                            <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"></path>
                                            <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"></path>
                                            <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"></path>
                                            <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"></path>
                                            <path fill="none" d="M0 0h48v48H0z"></path>
                                        </svg>
                                    </div>
                                    <span class="gsi-material-button-contents">Continue with Google</span>
                                </div>
                            </button>
                        </a>
                        
                        <div class="row justify-content-center p-2">
                            <a href="{{ url('/login') }}" class="btn btn-link change-card-link text-center w-auto p-0">Already has an account? <u>Login now!</u></a>
                            <p class="text-center change-card-link m-0">or</p>
                            <a href=" {{ url('/register/vendor') }}" class="btn btn-link change-card-link text-center w-auto p-0">Join Eatwell as a <u>vendor!</u></a>
                        </div>
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
    <script src="https://apis.google.com/js/platform.js" async defer></script>
@endsection
