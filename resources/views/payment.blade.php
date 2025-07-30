@extends('master')

@section('title', 'Payment')

@section('css')
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/payment.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:FILL@1" rel="stylesheet" />
@endsection

@section('content')
    <div class="container">
        <h1 class="lexend font-semi-bold text-white your-order mt-3">Your Order</h1>
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

            {{-- CSRF token untuk AJAX POST request --}}
            <meta name="csrf-token" content="{{ csrf_token() }}">

            <div class="orderdet">
                <p class="lexend font-semibold text-white judul">Order Detail</p>
            </div>
            <div class="detail">
                <p class="lexend font-medium text-black que">Active Period:</p>
                <p class="lexend font-bold text-black ans">{{ $startDate }} until {{ $endDate }}</p>
            </div>
            {{-- <div class="detail">
                <p class="lexend font-medium text-black que">Order Date & Time:</p>
                <p class="lexend font-bold text-black ans">{{ \Carbon\Carbon::now()->format('h:i A || D, d M Y') }}</p>
            </div> --}}
            <hr
                style="height: 1.5px; background-color:black; opacity:100%; border: none; margin-left: 20px; margin-right: 20px;">

            @foreach ($cartDetails as $packageDetail)
                <div class="fullord">
                    <p class="inter font-bold text-black detail pack-name mt-3">{{ $packageDetail['package_name'] }}</p>
                    <div class="container lexend font-regular text-black">
                        <div class="row align-items-start">
                            <div class="col text-left pack-list">
                                @if ($packageDetail['breakfast_qty'] > 0)
                                    <p>{{ $packageDetail['breakfast_qty'] }}<span>x </span>Breakfast</p>
                                @endif
                                @if ($packageDetail['lunch_qty'] > 0)
                                    <p>{{ $packageDetail['lunch_qty'] }}<span>x </span>Lunch</p>
                                @endif
                                @if ($packageDetail['dinner_qty'] > 0)
                                    <p>{{ $packageDetail['dinner_qty'] }}<span>x </span>Dinner</p>
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
                <p class="inter font-semibold text-black detail pack-name mb-0">Payment Method</p>
                <div class="button-payment lexend font-medium text-black">
                    @foreach ($paymentMethod as $payment)
                        <div class="form-check m-0">
                            <input class="form-check-input radio-custom" type="radio" name="payment-button" id="{{ Str::slug($payment->name) }}"
                                value="{{ $payment->methodId }}">
                            <label class="form-check-label" for="{{ Str::slug($payment->name) }}">
                                {{ $payment->name }}
                            </label>
                        </div>
                    @endforeach
                    {{-- <div class="form-check m-0">
                        <input class="form-check-input radio-custom" type="radio" name="payment-button" id="wellpay"
                            value="1">
                        <label class="form-check-label" for="wellpay">
                            Wellpay
                        </label>
                    </div>
                    <div class="form-check m-0">
                        <input class="form-check-input radioButtonPayment radio-custom" type="radio" name="payment-button"
                            id="qris" value="2">
                        <label class="form-check-label" for="qris">
                            QRIS
                        </label>
                    </div>
                    <div class="form-check m-0">
                        <input class="form-check-input radio-custom" type="radio" name="payment-button" id="bva"
                            value="3">
                        <label class="form-check-label" for="bva">
                            BCA Virtual Account
                        </label>
                    </div> --}}
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
            <button type="button" class="inter font-semibold text-white pay-btn" id="mainPayButton">Pay</button>
        </div>
    </div>

    <div id="qrisPopup" class="popup-overlay">
        <div class="popup-content" style="width: fit-content;">
            <h2>Pay Now</h2>
            <div class="qr-code-container">
                <img src="" alt="QR Code" id="qrCodeImage">
            </div>
            <p class="timer">Expires in <span id="countdownTimer">00:59</span></p>
            <div class="d-flex">
                <button class="popup-button download-qris me-3" id="downloadQrisBtn">Download QRIS</button>
                <button class="popup-button done" id="doneBtn">Done</button>
            </div>
        </div>
    </div>

    <div id="confirmationPopup" class="popup-overlay">
        <div class="popup-content" style="width: fit-content;">
            <p class="inter font-semibold", style="color: red; font-size:20px">Warning</p>
            <p style="font-weight:600; color:#222; text-align:center; margin-bottom:24px;">
                Are you sure you want to proceed with this payment?
            </p>
            <button id="confirmBtn" class="popup-button"
                style="background:#E77133; color:white; border:none; border-radius:24px; padding:12px 32px; font-size:18px; font-weight:500; box-shadow:0 2px 6px #0001;">
                Confirm Payment
            </button>
        </div>
    </div>

    <div id="wellpayConfirmPopup" class="popup-overlay">
        <div class="popup-content" style="width: fit-content">
            {{-- Stage 1: Initial Confirmation --}}
            <div id="wellpayStage1" class="text-center">
                <p class="inter font-semibold" style="color: green; font-size:20px">Confirm Wellpay Payment</p>
                <p id="wellpayBalanceText" style="font-weight:400; color:#222; text-align:center; margin-bottom:12px;">
                    Your current Wellpay balance is: Rp X.XXX.XXX,-
                </p>
                <p style="font-weight:400; color:#222; text-align:center; margin-bottom:24px;">
                    Are you sure you want to pay with Wellpay?
                </p>
            </div>

            {{-- Stage 2: Password Input (Hidden by default) --}}
            <div id="wellpayStage2" style="display: none;">
                <p class="inter font-semibold" style="color: green; font-size:20px">Enter Your Password</p>
                <p id="wellpayAmountToPay" style="font-weight:400; color:#222; text-align:center; margin-bottom:12px;">
                    Total to pay: Rp {{ number_format($totalOrderPrice, 0, ',', '.') }}
                </p>
                <div class="mb-3">
                    <label for="wellpayPasswordInput" class="form-label visually-hidden">Password</label>
                    <input type="password" class="form-control" id="wellpayPasswordInput" placeholder="Enter your account's password">
                    <div id="wellpayPasswordError" class="text-danger mt-1" style="display: none;"></div>
                </div>
            </div>

            <div id="wellpayPopupMessage" class="mt-3 text-center" style="display: none;"></div>

            <div class="d-flex justify-content-center">
                <button id="wellpayCancelBtn" class="popup-button me-3 mt-0"
                    style="background:#f44336; color:white; border:none; border-radius:10px; padding:5px 32px; font-size:18px; font-weight:500; box-shadow:0 2px 6px #0001;">
                    Cancel
                </button>
                <button id="wellpayConfirmBtn" class="popup-button mt-0 d-flex align-items-center justify-content-center"
                    style="background:#4CAF50; color:white; border:none; border-radius:10px; padding:5px 32px; font-size:18px; font-weight:500; box-shadow:0 2px 6px #0001;">
                    Confirm
                </button>
            </div>
        </div>
    </div>

    <div id="successPopup" class="popup-overlay">
        <div class="popup-content" style="width: fit-content">
            <p style="font-weight:600; color:#222; text-align:center; margin-bottom:24px;">
                Successfully added to your subscription. Thank you for your purchase.
            </p>
            <a href={{route('order-history')}} id="backHomeBtn" class="popup-button"
                style="background:#E77133; color:white; border:none; border-radius:24px; padding:12px 32px; font-size:18px; font-weight:500; box-shadow:0 2px 6px #0001; text-decoration: none;">
                See my order
            </a>
        </div>
    </div>

    <div id="customMessageBox" class="message-box-overlay">
        <div class="message-box-content">
            <p id="messageBoxText">Please select a payment method.</p>
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
