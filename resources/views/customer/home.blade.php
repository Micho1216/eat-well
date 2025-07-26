@extends('master')

@section('title', 'Home')

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/js/app.js', 'resources/sass/app.scss'])
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="stylesheet" href="{{ asset('css/cardVendor.css') }}">
    <link rel="stylesheet" href="{{ asset('css/customer/home.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@php
    use Carbon\Carbon;
@endphp

@section('content')
    <main class="">
        {{-- Carousel/Image --}}
        <img src="{{ asset('asset/customer/home/iklan 1.jpg') }}" class="img-fluid" alt="..."
            style="width: 100%; max-height: 350px; object-fit: cover;">
        <main class="container pt-3 pb-3 lexend">
            {{-- Untuk button ini jangan dihapus, untuk sementara button logout disini, menunggu UI logout beneran dibuat --}}
            <form action="{{ route('logout') }}" method="post">
                @csrf
                <button type="submit"></button>
            </form>
            {{-- WellPay --}}
            <div class="wellpay-container mb-4">
                <div class="d-flex align-items-center mb-2 lexend">
                    <span><img src="{{ asset('asset/navigation/eatwellLogo.png') }}" alt="logo"
                            style="width: 3.5vh; background-color: var(--bg-primary); border-radius: 5px;"></span>
                    <span>
                        <h4 class="mb-0 ms-2">WellPay</h4>
                    </span>
                </div>

                <div class="d-flex align-items-center">
                    <span>
                        <h1 class="inter m-0 me-3 ms-2">Rp <span
                                id="wellpayBalanceAmount">{{ number_format($wellpay, 2, ',', '.') }}</span></h1>
                    </span>
                    <div class="d-flex align-items-center justify-content-center" id="toggleVisibilityBtn"
                        style="width: 30px; height: 30px; border: 3px solid var(--bg-primary); border-radius: 50%; background: var(--bg-primary);">
                        <span class="material-symbols-outlined" id="visibilityIcon"
                            style="font-variation-settings: 'FILL' 1; font-size: 20px; color: #fff; cursor: pointer;">visibility_off</span>
                    </div>
                </div>

                <div class="d-flex align-items-center mt-2" id="topUpButton">
                    {{-- Custom Modal 1 --}}
                    <div id="customModal1" class="custom-modal-overlay">
                        <div class="custom-modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="customModal1Title">{{ __('customer/home.add_balance') }}
                                </h1>
                                <button type="button" class="btn-close" id="closeCustomModal1" aria-label="Close">
                                    <span class="material-symbols-outlined">close</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <h5>{{ __('customer/home.curr_balance') }}:
                                    <span class="fw-bold">
                                        Rp {{ number_format($wellpay, 2, ',', '.') }}
                                    </span>
                                </h5>
                                <input type="hidden" id="currentBalanceValue" value="{{ $wellpay }}">
                                {{-- <form action="" method="POST" id="topUpForm"> --}}
                                {{-- @csrf --}}
                                <div class="mb-3">
                                    <label for="topupAmount"
                                        class="form-label mt-2">{{ __('customer/home.enter_amount') }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" class="form-control" id="topupAmount" name="topupAmount"
                                            autocomplete="off">
                                    </div>
                                    <div id="topupError" class="text-danger mt-1"
                                        style="font-size: 0.875em; display: none;">
                                    </div>
                                </div>
                                {{-- </form> --}}
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-primary"
                                    id="nextCustomModalBtn">{{ __('customer/home.continue') }}</button>
                            </div>
                        </div>
                    </div>

                    {{-- Custom Modal 2 --}}
                    <div id="customModal2" class="custom-modal-overlay">
                        <div class="custom-modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="customModal2Title">{{ __('customer/home.confirm') }}
                                    Top-up</h1>
                                <button type="button" class="btn-close" id="closeCustomModal2" aria-label="Close">
                                    <span class="material-symbols-outlined">close</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form>
                                    <h6 class="m-0 inter">{{ __('customer/home.confirm_amount') }} top up: <span
                                            id="confirmTopupAmount" class="fw-bold"></span></h6>
                                    <h6 class="m-0 inter">{{ __('customer/home.confirm_new') }}<span id="confirmNewBalance"
                                            class="fw-bold"></span></h6>
                                    <input type="hidden" id="finalTopupAmount" value="">
                                    <hr>
                                    <div class="mb-3">
                                        <label for="accountPassword"
                                            class="form-label">{{ __('customer/home.confirm_enterpass') }}</label>
                                        <input type="password" class="form-control" id="accountPassword"
                                            name="accountPassword" placeholder="Your password"
                                            style="border-top-left-radius: 0.25rem; border-bottom-left-radius: 0.25rem;"
                                            autocomplete="current-password">
                                        <div id="passwordError" class="text-danger mt-1"
                                            style="font-size: 0.875em; display: none;"></div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-secondary"
                                    id="backToCustomModal1">{{ __('customer/home.back') }}</button>
                                <button class="btn btn-primary"
                                    id="confirmTopupBtn">{{ __('customer/home.confirm') }}</button>
                            </div>
                        </div>
                    </div>


                    <button class="btn d-flex justify-content-center align-items-center m-0" id="openCustomModal1"
                        style="width: 107px; background-color: var(--bg-contrast); padding: 5px; border-radius: 100px; color: #fff; cursor: pointer;">
                        <span class="me-2">Top up</span>
                        <div class="d-flex align-items-center justify-content-center"
                            style="width: 20px; height: 20px; border: 2px solid #fff; border-radius: 50%; background: var(--bg-contrast);">
                            <span class="material-symbols-outlined" style="font-size: 18px; color: #fff;">add</span>
                        </div>
                    </button>
                </div>
            </div>

            <div id="successToast" class="success-toast">
                <span class="material-symbols-outlined check-icon">check_circle</span>
                <p id="successToastMessage" class="toast-message">Top-up {{ __('customer/home.success') }}!</p>
            </div>
            {{-- Active Subscription Card --}}
            <section class="container w-100 mt-3 h-auto mb-md-5 mb-4 subscription-card p-4">
                @if ($order)
                    <div class="row mb-2 justify-content-between align-content-end">
                        <div class="title font-400 w-auto">{{ __('customer/home.subs_card_header') }}</div>
                        <div class="hug-content detail d-flex align-self-end">
                            <a href="{{ route('order-history') }}"
                                class="detail-link">{{ __('customer/home.view_all') }}</a>
                        </div>
                    </div>
                    <div class="row mb-2 gy-1">
                        <div class="col-6 col-sm-3 subscription-detail">
                            <div class="row sub-title">{{ __('customer/home.active_from') }}</div>
                            <div class="row content ps-sm-3">{{ Carbon::parse($order->startDate)->format('d M Y') }}</div>
                        </div>
                        <div class="col-6 col-sm-3 col-lg-9 subscription-detail">
                            <div class="row sub-title">{{ __('customer/home.active_until') }}</div>
                            <div class="row content ps-sm-3">{{ Carbon::parse($order->endDate)->format('d M Y') }}</div>
                        </div>
                        <div class="col-sm subscription-detail">
                            <div class="row sub-title">{{ __('customer/home.rec_name') }}</div>
                            <div class="row content ps-sm-3">{{ $order->recipient_name }}</div>
                        </div>
                        <div class="col col-sm-9 subscription-detail">
                            <div class="row sub-title">{{ __('customer/home.delivery_adr') }}</div>
                            <div class="row content ps-sm-3">{{ $order->jalan . ', ' . $order->kota }}</div>
                        </div>

                    </div>
                    <div class="row catering-name font-400 pb-2">
                        {{ $order->vendor->name }}
                    </div>
                    <x-order-slots :slotMap="$slotMap" />
                @else
                    <div class="d-flex flex-column justify-content-center align-items-center gap-2">
                        <h2 class="text-center fw-500 mb-0 mt-2">{{__('customer/home.no_order')}}</h2>
                        <a href="{{ route('search') }}" class="btn btn-warning">Order Now</a>
                    </div>
                @endif
            </section>
            {{-- Popular Caterings --}}
            <section class="container mt-3 popular-catering-container w-100 h-auto mb-md-5 mb-4">
                <div class="row title-1">{{__('customer/home.popular')}}</div>
                <div class="row">
                    @foreach ($vendors as $vendor)
                        <div class="col-md-6 col-xl-4 p-2">
                            <x-card-vendor :vendor="$vendor"></x-card-vendor>
                        </div>
                    @endforeach
                </div>
            </section>
            {{-- Favorited Catering or Recently Viewed? --}}
            {{-- @if (!$favVendors->isEmpty())
                <section class="container fav-catering-container mt-3 w-100 h-auto mb-md-5 mb-4">
                    <div class="section-title-wrap d-flex flex-row justify-content-between align-items-center">
                        <h3 class="title-1">Your Favorites</h3>
                        @if ($favVendors->count() > 4)
                            <a href="{{route('favorite.show')}}" class="detail-link">View all</a>
                        @endif
                    </div>
                    <div class="carousel-slider-wrap carousel-style-1 mt-10 align-self-center">
                        <ul class="carousel-product-list">
                            @foreach ($favVendors as $vendor)
                                <li id="fav-card-{{ $vendor->vendorId }}">
                                    <x-card-vendor :vendor="$vendor"></x-card-vendor>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </section>
            @endif --}}
        </main>
    @endsection

    @section('scripts')
        <script src="{{ asset('js/customer/favoriteCatering.js') }}"></script>
        {{-- <script src="{{ asset('js/customer/view-wellpay.js') }}"></script> --}}
        <script src="{{ asset('js/customer/wellpay.js') }}"></script>
    @endsection
