@extends('master')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap">
    <link rel="stylesheet" href="{{ asset('css/password.css') }}">
@endsection

@section('content')
    <div class="container-fluid p-5vh-100">
        <div class="row p-5">
            <div class="col-12">
                <div class="card p-5 rounded-3">
                    <div class="row">
                        <div class="col-12 col-md-6 d-flex flex-column align-items-center">
                                <h2 class="card-title border my-3 mb-3 text-center align-text-center align-self-center">{{ __('auth/reset-password.title') }}</h2>
                                <img src="{{ asset('asset/password/icon_eatwell.png') }}" class="img-thumbnail w-50" width="200" height="200" alt="">
                        </div>
                        <div class="col-12 col-md-6 d-flex flex-center justify-content-center align-items-center border-black px-0 px-md-5 py-2">
                            <form class="w-100" action="{{ route('password.update') }}" method="post">
                                @csrf
                                <div class="form-floating mb-3 mt-3">
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="">
                                    <label for="password">{{ __('auth/reset-password.password') }}</label>
                                    <div class="invalid-feedback">{{ $errors->first('password') }}</div>
                                </div>
                                <div class="form-floating mb-4">
                                    <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" id="password_confirmation" name="password_confirmation" placeholder="">
                                    <label for="password_confirmation">{{ __('auth/reset-password.password_confirmation') }}</label>
                                    <div class="invalid-feedback">{{ $errors->first('password_confirmation') }}</div>
                                </div>
                                <input type="hidden" name="token" value="{{ $token }}">
                                <input type="hidden" name="email" value="{{ $email }}">
                
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="mt-3 me-3 mb-2 px-md-5 d-flex btn btn-dark">{{ __('auth/reset-password.continue') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    </div>
                </div>
            </col>

        </div>
    </div>
@endsection

@section('scripts')
@endsection