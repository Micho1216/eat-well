@extends('master')

@section('title', 'Payment')

@section('css')
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/payment.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:FILL@1" rel="stylesheet" />
@endsection

@php
    use Carbon\Carbon;
    Carbon::setLocale(app()->getLocale());
@endphp

@section('content')
    <div id="translation-data"
        data-wellpay-balance-text="{{ __('customer/payment.wellpay_balance') }}"
        data-wellpay-confirm-btn="{{ __('customer/payment.confirm') }}"
        data-select-paymeth-text="{{ __('customer/payment.select_paymeth') }}"
        data-no-paymeth-text="{{ __('customer/payment.no_paymeth') }}"
        data-missing-order-text="{{ __('customer/payment.missing_order') }}"
        data-unknown-error-text="{{ __('customer/payment.unknown_error') }}"
        data-checkout-failed-text="{{ __('customer/payment.checkout_failed') }}"
        data-wellpay-insufficient-text="{{ __('customer/payment.wellpay_insufficient') }}"
        data-wellpay-format-error-text="{{ __('customer/payment.wellpay_format_error') }}"
        data-insufficient-wellpay-balance-text="{{ __('customer/payment.insufficient_wellpay_balance') }}"
        data-your-balance-text="{{ __('customer/payment.your_balance')}}"
        data-amount-pay-text="{{ __('customer/payment.amount_pay') }}"
        data-retrieve-wellpay-balance-text="{{ __('customer/payment.retrieve_wellpay_balance') }}"
        data-retrieve-check-connection-text="{{ __('customer/payment.retrieve_check_connection') }}"
        data-enter-pass-text="{{ __('customer/payment.enter_pass')}}"
        data-wellpay-cancel-text="{{ __('customer/payment.wellpay_cancel') }}"
        data-continue-text="{{ __('customer/payment.continue')}}"
        >
    </div>
    <div class="container">
        <h1 class="lexend font-semi-bold text-white your-order mt-3">{{ __('customer/payment.your_order') }}</h1>
        <h4 class= "jalan mb-1">
            <span class="material-symbols-outlined location-icon">pin_drop</span>
            <span class="lexend font-regular text-white">{{ $selectedAddress->jalan }}, {{ $selectedAddress->kelurahan }}, {{ $selectedAddress->kecamatan }}, {{ $selectedAddress->kota }}, {{ $selectedAddress->provinsi }}, {{ $selectedAddress->kode_pos }}</span>
        </h4>
        @if ($selectedAddress->notes)
            <p class="lexend font-regular text-white">
                {{ $selectedAddress->notes }}
            </p> 
        @endif
        <div class="container-sm isi">
            <input type="hidden" id="hiddenVendorId" value="{{ $vendor->vendorId }}">
            <input type="hidden" id="hiddenStartDate" value="{{ $startDate }}">
            <input type="hidden" id="hiddenEndDate" value="{{ $endDate }}">
            <input type="hidden" id="hiddenCartTotalPrice" value="{{ $totalOrderPrice }}">
            <input type="hidden" id="hiddenSelectedAddressProvinsi" value="{{ $selectedAddress->provinsi }}">
            <input type="hidden" id="hiddenSelectedAddressKota" value="{{ $selectedAddress->kota }}">
            <input type="hidden" id="hiddenSelectedAddressKabupaten" value="{{ $selectedAddress->kota }}">
            <input type="hidden" id="hiddenSelectedAddressKecamatan" value="{{ $selectedAddress->kecamatan }}">
            <input type="hidden" id="hiddenSelectedAddressKelurahan" value="{{ $selectedAddress->kelurahan }}">
            <input type="hidden" id="hiddenSelectedAddressKodePos" value="{{ $selectedAddress->kode_pos }}">
            <input type="hidden" id="hiddenSelectedAddressJalan" value="{{ $selectedAddress->jalan }}">
            <input type="hidden" id="hiddenSelectedAddressRecipientName" value="{{ $selectedAddress->recipient_name }}">
            <input type="hidden" id="hiddenSelectedAddressRecipientPhone" value="{{ $selectedAddress->recipient_phone}}">
            <input type="hidden" id="hiddenSelectedAddressNotes" value="{{ $selectedAddress->notes }}">

            {{-- Ini akan dieksekusi oleh Blade Engine Laravel --}}

            {{-- Pastikan juga ada CSRF token untuk AJAX POST request --}}
            <meta name="csrf-token" content="{{ csrf_token() }}">

            <div class="orderdet">
                <p class="lexend font-semibold text-white judul">{{ __('customer/payment.order_detail') }}</p>
            </div>
            <div class="detail">
                <p class="lexend font-medium text-black que">{{ __('customer/payment.active_period') }}:</p>
                <p class="lexend font-bold text-black ans">{{ $startDate }} {{ __('customer/payment.until') }} {{ $endDate }}</p>
            </div>
            <div class="detail">
                <p class="lexend font-medium text-black que">{{ __('customer/payment.order_date_time') }}:</p>
                {{-- <p class="lexend font-bold text-black ans">06:00 AM Sat, 01 May 2025</p> --}}
                <p class="lexend font-bold text-black ans">{{ Carbon::now()->translatedFormat('H:i || D, d M Y') }}</p>
            </div>
            <hr
                style="height: 1.5px; background-color:black; opacity:100%; border: none; margin-left: 20px; margin-right: 20px;">

            @foreach ($cartDetails as $packageDetail)
                <div class="fullord">
                    <p class="inter font-bold text-black detail pack-name mt-3">{{ $packageDetail['package_name'] }}</p>
                    <div class="container lexend font-regular text-black">
                        <div class="row align-items-start">
                            <div class="col text-left pack-list">
                                @if ($packageDetail['breakfast_qty'] > 0)
                                    <p>{{ $packageDetail['breakfast_qty'] }}<span>x </span>{{ __('customer/payment.breakfast') }}</p>
                                @endif
                                @if ($packageDetail['lunch_qty'] > 0)
                                    <p>{{ $packageDetail['lunch_qty'] }}<span>x </span>{{ __('customer/payment.lunch') }}</p>
                                @endif
                                @if ($packageDetail['dinner_qty'] > 0)
                                    <p>{{ $packageDetail['dinner_qty'] }}<span>x </span>{{ __('customer/payment.dinner') }}</p>
                                @endif
                            </div>
                            <div class="col pack-price text-right">
                                @if ($packageDetail['breakfast_qty'] > 0)
                                    <p>Rp {{ number_format($packageDetail['breakfast_price'], 2, ',', '.') }}</p>
                                @endif
                                @if ($packageDetail['lunch_qty'] > 0)
                                    <p>Rp {{ number_format($packageDetail['lunch_price'], 2, ',', '.') }}</p>
                                @endif
                                @if ($packageDetail['dinner_qty'] > 0)
                                    <p>Rp {{ number_format($packageDetail['dinner_price'], 2, ',', '.') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <hr
                style="height: 1.5px; background-color:black; opacity:100%; border: none; margin-left: 20px; margin-right: 20px;">
            <div class="payment-meth">
                <p class="inter font-semibold text-black detail pack-name mb-0">{{ __('customer/payment.payment_method') }}</p>
                <div class="button-payment lexend font-medium text-black">
                    <div class="form-check m-0">
                        <input class="form-check-input radio-custom" type="radio" name="payment-button" id="wellpay"
                            value="1">
                        <label class="form-check-label" for="wellpay">
                            WellPay
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input radioButtonPayment radio-custom" type="radio" name="payment-button"
                            id="qris" value="2">
                        <label class="form-check-label" for="qris">
                            QRIS
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input radio-custom" type="radio" name="payment-button" id="bva"
                            value="3">
                        <label class="form-check-label" for="bva">
                            BCA Virtual Account
                        </label>
                    </div>
                </div>
            </div>
            <hr
                style="height: 3px; background-color:black; opacity:100%; border: none; margin-left: 20px; margin-right: 20px;">
            <div class="inter font-medium text-black total">
                <span class="detail">Total</span>
                <span class="font-bold nominal">Rp {{ number_format($totalOrderPrice, 2, ',', '.') }}</span>
            </div>
        </div>
        <div class="pay-button">
            <button type="button" class="inter font-semibold text-white pay-btn" id="mainPayButton">{{ __('customer/payment.pay_btn') }}</button>
        </div>
    </div>

    <div id="qrisPopup" class="popup-overlay">
        <div class="popup-content" style="width: fit-content;">
            <h2>{{ __('customer/payment.pay_now') }}</h2>
            <div class="qr-code-container">
                <img src="" alt="QR Code" id="qrCodeImage">
            </div>
            <p class="timer" ><span id="expiresInMess">{{ __('customer/payment.expires_in') }}</span><span id="countdownTimer">00:59</span></p>
            <div class="d-flex">
                <button class="popup-button download-qris me-3" id="downloadQrisBtn">{{ __('customer/payment.download') }} QRIS</button>
                <button class="popup-button done" id="doneBtn">{{ __('customer/payment.done') }}</button>
            </div>
        </div>
    </div>

    <div id="qrisPopupCancelled" class="popup-overlay">
        <div class="popup-content" style="width: fit-content;">
            <h6>{{ __('customer/payment.expired_qris') }}<h6>
            <div class="d-flex">
                <button class="popup-button download-qris me-3" id="closeBtn">{{ __('customer/payment.close') }}</button>
            </div>
        </div>
    </div>

    <div id="confirmationPopup" class="popup-overlay">
        <div class="popup-content" style="width: fit-content;">
            <p class="inter font-semibold", style="color: red; font-size:20px">{{ __('customer/payment.warning') }}</p>
            <p style="font-weight:600; color:#222; text-align:center; margin-bottom:24px;">
                {{ __('customer/payment.warning_content') }}
            </p>
            <button id="confirmBtn" class="popup-button"
                style="background:#E77133; color:white; border:none; border-radius:24px; padding:12px 32px; font-size:18px; font-weight:500; box-shadow:0 2px 6px #0001;">
                {{ __('customer/payment.confirm_va') }}
            </button>
        </div>
    </div>

    <div id="wellpayConfirmPopup" class="popup-overlay">
        <div class="popup-content" style="width: fit-content">
            {{-- Stage 1: Initial Confirmation --}}
            <div id="wellpayStage1" class="text-center">
                <p class="inter font-semibold" style="color: green; font-size:20px">{{ __('customer/payment.confirm_wellpay') }}</p>
                <p id="wellpayBalanceText" style="font-weight:400; color:#222; text-align:center; margin-bottom:12px;">
                    {{ __('customer/payment.wellpay_balance') }}: Rp X.XXX.XXX,-
                </p>
                <p style="font-weight:400; color:#222; text-align:center; margin-bottom:24px;">
                    {{ __('customer/payment.wellpay_yousure') }}
                </p>
            </div>

            {{-- Stage 2: Password Input (Hidden by default) --}}
            <div id="wellpayStage2" style="display: none;">
                <p class="inter font-semibold" style="color: green; font-size:20px">{{ __('customer/payment.enter_pass') }}</p>
                <p id="wellpayAmountToPay" style="font-weight:400; color:#222; text-align:center; margin-bottom:12px;">
                    {{ __('customer/payment.amount_pay') }}: Rp {{ number_format($totalOrderPrice, 0, ',', '.') }}
                </p>
                <div class="mb-3">
                    <label for="wellpayPasswordInput" class="form-label visually-hidden">{{ __('customer/payment.pass_label') }}</label>
                    <input type="password" class="form-control" id="wellpayPasswordInput" placeholder= "{{ __('customer/payment.acc_pass') }}">
                    <div id="wellpayPasswordError" class="text-danger mt-1" style="display: none;"></div>
                </div>
            </div>

            <div id="wellpayPopupMessage" class="mt-3 text-center" style="display: none;"></div>

            <div class="d-flex justify-content-center">
                <button id="wellpayCancelBtn" class="popup-button me-3 mt-0"
                    style="background:#f44336; color:white; border:none; border-radius:10px; padding:5px 32px; font-size:18px; font-weight:500; box-shadow:0 2px 6px #0001;">
                    {{ __('customer/payment.cancel') }}
                </button>
                <button id="wellpayConfirmBtn" class="popup-button mt-0"
                    style="background:#4CAF50; color:white; border:none; border-radius:10px; padding:5px 32px; font-size:18px; font-weight:500; box-shadow:0 2px 6px #0001;">
                    {{ __('customer/payment.confirm') }}
                </button>
            </div>
        </div>
    </div>

    <div id="successPopup" class="popup-overlay">
        <div class="popup-content" style="width: fit-content">
            <p style="font-weight:600; color:#222; text-align:center; margin-bottom:24px;">
                {{ __('customer/payment.success_desc') }}
            </p>
            <button id="backHomeBtn" class="popup-button"
                style="background:#E77133; color:white; border:none; border-radius:24px; padding:12px 32px; font-size:18px; font-weight:500; box-shadow:0 2px 6px #0001;">
                {{ __('customer/payment.back_btn') }}
            </button>
        </div>
    </div>

    <div id="customMessageBox" class="message-box-overlay">
        <div class="message-box-content">
            <p id="messageBoxText">{{ __('customer/payment.select_payment') }}</p>
            <button id="messageBoxOkBtn">OK</button>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Pastikan objek global App ada
        window.App = window.App || {};
        window.App.routes = {
            checkoutProcess: '{{ route('checkout.process') }}',
            userWellpayBalance: '{{ route('user.wellpay.balance') }}',
        };
    </script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="{{ asset('js/payment.js') }}"></script>
@endsection
