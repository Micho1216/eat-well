@extends('master')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap">
@endsection

@section('content')
    <div class="container-fluid p-5 vh-100 bg-success-subtle">
        <div class="row p-5">
            <h2>Forgot your password?</h2>
            <form action="{{ route('password.update') }}" method="post">
                @csrf
                <div class="form-floating mb-3">
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="">
                    <label for="password">Password</label>
                    <div class="invalid-feedback">{{ $errors->first('password') }}</div>
                </div>
                <div class="form-floating mb-3">
                    <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" id="password_confirmation" name="password_confirmation" placeholder="">
                    <label for="password_confirmation">Password Confirmation</label>
                    <div class="invalid-feedback">{{ $errors->first('password_confirmation') }}</div>
                </div>
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

            
                <button type="submit" class="btn btn-primary">Continue</button>
            </form>

        </div>
    </div>
@endsection

@section('scripts')
@endsection