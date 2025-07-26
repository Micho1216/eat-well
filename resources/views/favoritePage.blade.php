@extends('master')

@section('title', 'EatWell | Favorited')

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/js/app.js', 'resources/sass/app.scss'])
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="stylesheet" href="{{ asset('css/cardVendor.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/customer/favoritePage.css') }}">
@endsection

@section('content')
    <section class="container d-flex flex-row gap-3 justify-content-start align-items-center mt-3 mb-2">
        <div class="rounded-circle bg-white d-flex justify-content-center align-content-center"
            style="width: 40px; height: 40px;">
            <a href="{{url()->previous()}}" class="btn material-symbols-outlined icon-fill pt-2" style="color: #E77133;">
                arrow_back_ios_new
            </a>
        </div>
        <h1 class="text-left m-0">{{__('customer/favorites.heading')}}</h1>
    </section>
    <section class="container mb-3 fav-vendor-container">
        @if ($vendors->isEmpty())
            <div class="d-flex flex-row justify-content-center">
                <img src="{{asset('asset/empty-favorites.png')}}" class="rounded-4" alt="No Favorite vendors" width="250px" height="250px">
            </div>
        @else
            <div class="row">
                @foreach ($vendors as $vendor)
                    <div class="col-md-6 col-xl-4 p-2">
                        <x-card-vendor :vendor="$vendor"></x-card-vendor>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
@endsection

@section('scripts')
    <script src="{{ asset('js/customer/favoritePage.js') }}"></script>
@endsection
