@extends('master')

@section('title', 'Invalid Reset Password')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap">
    <link rel="stylesheet" href="{{ asset('css/password.css') }}">
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row p-5">
            <div class="col-12">
                <div class="card p-5 rounded-3">
                    <div class="row">
                        <div class="col-12 col-md-6 d-flex flex-column align-items-center">
                                <h2 class="card-title border my-3 mb-3 text-center align-text-center align-self-center">{{ __('auth/reset-password.invalid_title') }}</h2>
                                <img src="{{ asset('asset/password/icon_eatwell.png') }}" class="img-thumbnail w-50" width="200" height="200" alt="">
                        </div>
                        <div class="col-12 col-md-6 d-flex flex-center justify-content-center align-items-center border-black px-0 px-md-5 py-2">
                            <h3>{{ __('auth/reset-password.invalid_desc') }}</h4>
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