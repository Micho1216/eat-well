@extends('master')

@section('title', 'EatWell | Order Detail')

@php
    use Carbon\Carbon;
    Carbon::setLocale(app()->getLocale());
@endphp

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/js/app.js', 'resources/sass/app.scss'])
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/customer/orderHistory.css') }}">
    <link rel="stylesheet" href="{{ asset('css/customer/orderDetail.css') }}">
@endsection

@section('content')
    <main>
        @if (session('message'))
            <div id="flash-message" class="alert alert-success"
                style="position:fixed;top:20px;left:50%;transform:translateX(-50%);z-index:2000;min-width:250px;text-align:center;">
                {{ session('message') }}
            </div>
        @endif
        <div class="container">
            <div class="order-detail-card">
                {{-- Card Header --}}
                <section class="card-header">
                    <div class="left-container">
                        <a href="{{ route('order-history') }}" class="btn-back">
                            <span class="icon">&lt;</span>
                            <span class="">{{__('customer/order.back')}}</span>
                        </a>
                    </div>
                    <div class="right-container">
                        <div class="text-wrapper">
                            <span class="">Order ID. {{ $order->orderId }}</span>
                        </div>
                        <div class="text-wrapper label-status status-{{ $status }}">
                            {{ ucfirst(__('customer/order.' . $status)) }}
                        </div>
                    </div>
                </section>
                <section class="card-order-status">
                    <div class="order-status-progress d-flex align-items-center justify-content-between">
                        {{-- Order Placed --}}
                        <div class="status-step active">
                            <div class="circle">
                                <span class="material-symbols-outlined">shopping_cart</span>
                            </div>
                            <div class="label">{{__('customer/order.ostat_placed')}}</div>
                        </div>
                        <div class="status-line {{ $order->payment && $order->payment->paid_at ? 'active' : '' }}"></div>
                        {{-- Order Paid --}}
                        <div class="status-step {{ $order->payment && $order->payment->paid_at ? 'active' : '' }}">
                            <div class="circle">
                                <span class="material-symbols-outlined">payments</span>
                            </div>
                            <div class="label">{{__('customer/order.ostat_paid')}}</div>
                        </div>
                        <div class="status-line {{ now()->gt($order->startDate) ? 'active' : '' }}"></div>
                        {{-- Subscription Active --}}
                        <div class="status-step {{ now()->gt($order->startDate) ? 'active' : '' }}">
                            <div class="circle">
                                <span class="material-symbols-outlined">autorenew</span>
                            </div>
                            <div class="label">{{__('customer/order.ostat_active')}}</div>
                        </div>
                        <div class="status-line {{ now()->gt($order->endDate) ? 'active' : '' }}"></div>
                        {{-- Subscription Finished --}}
                        <div class="status-step {{ now()->gt($order->endDate) ? 'active' : '' }}">
                            <div class="circle">
                                <span class="material-symbols-outlined">check_circle</span>
                            </div>
                            <div class="label">{{__('customer/order.ostat_finished')}}</div>
                        </div>
                    </div>
                </section>

                {{-- card-delivery-status section --}}

                <section class="card-delivery-status mt-4">
                    <div class="cds-status-flex">
                        {{-- LEFT: Delivery Address (unchanged) --}}
                        <div class="cds-status-left-container flex-grow-1 pe-xl-5 mb-3 mb-lg-0">
                            <div class="cds-address-title">{{__('customer/order.adr_header')}}</div>
                            <div class="cds-address-recipient">
                                <h5 class="recipient-name">{{ $order->recipient_name }}</h5>
                                <div class="d-flex flex-row gap-2 align-items-center">
                                    <span class="material-symbols-outlined" style="font-size: 18px;">phone</span>
                                    <p class="recipient-phone">{{ $order->recipient_phone }}</p>
                                </div>
                                <div class="d-flex flex-row gap-2">
                                    <span class="material-symbols-outlined" style="font-size: 18px;">location_on</span>
                                    <p class="recipient-address">
                                        {{ $order->jalan . ', ' . $order->kelurahan . ', ' . $order->kecamatan . ', ' . $order->kabupaten . ', ' . $order->provinsi . ', ' . $order->kode_pos }}
                                    </p>
                                </div>
                                @if ($order->notes)
                                    <p class="recipient-address">
                                        Notes: {{ $order->notes }}
                                    </p>
                                @endif
                            </div>
                            @if ($status == 'finished')
                                <div class="rating-container mt-3" data-order-id="{{ $order->orderId }}">
                                    <span class="cds-address-title">
                                        @if ($order->vendorReview)
                                            {{__('customer/order.rated')}}
                                        @else
                                            {{__('customer/order.rate')}}
                                        @endif
                                    </span>
                                    @if ($order->vendorReview)
                                        <div class="container-fluid m-0 mt-1 p-2 rounded-2 d-flex flex-column gap-1"
                                            style="background-color: #ecedec;">
                                            <div class="d-flex flex-row align-items-center gap-1">
                                                <span class="material-symbols-outlined star-icon choosen }}"
                                                    style="cursor:default; font-size: 24px;">star</span>
                                                <span style="font-size: 16px;">{{ $order->vendorReview->rating }}</span>
                                            </div>
                                            <span style="font-size: 14px;">{{ $order->vendorReview->review }}</span>
                                        </div>
                                    @else
                                        <div class="rating-icon-list">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <button type="button" class="material-symbols-outlined star-icon-btn"
                                                    data-index="{{ $i }}">star</button>
                                            @endfor
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                        {{-- RIGHT: Day Filter, Date, Carousel/Slider --}}
                        <div class="cds-status-right-container flex-grow-2">
                            {{-- MOBILE: Carousel with indicators --}}
                            <div class="cds-delivery-carousel d-block d-lg-none">
                                <div id="cdsCarousel" class="carousel slide">
                                    <div class="carousel-indicators cds-carousel-indicators">
                                        @foreach ($slots as $slotKey => $slot)
                                            @if (!empty($statusesBySlot[$slot['key']]))
                                                <button type="button" data-bs-target="#cdsCarousel"
                                                    data-bs-slide-to="{{ $loop->index }}"
                                                    class="cds-carousel-indicator-btn {{ $loop->first ? 'active' : '' }}"
                                                    aria-current="{{ $loop->first ? 'true' : 'false' }}"
                                                    aria-label="{{ $slot['label'] }}">
                                                    <span class="material-symbols-outlined">{{ $slot['icon'] }}</span>
                                                </button>
                                            @endif
                                        @endforeach
                                    </div>
                                    <div class="carousel-inner">
                                        @php $slotIdx = 0; @endphp
                                        @foreach ($slots as $slotKey => $slot)
                                            @if (!empty($statusesBySlot[$slot['key']]))
                                                <div class="carousel-item {{ $slotIdx === 0 ? 'active' : '' }}">
                                                    <div class="cds-delivery-slot-card mx-auto">
                                                        <div class="cds-slot-title text-center mb-2">{{ $slot['label'] }}
                                                        </div>
                                                        <div class="cds-slot-status-list">
                                                            @foreach ($statusesBySlot[$slot['key']] as $date => $deli_status)
                                                                <div
                                                                    class="cds-slot-status-row {{ $deli_status->status->value ?? $deli_status->status }}">
                                                                    <div
                                                                        class="cds-circle-icon d-flex align-items-center justify-content-center">
                                                                        {{-- Choose icon based on status --}}
                                                                        <span
                                                                            class="material-symbols-outlined status-icon-sm">
                                                                            @if ($deli_status->status->value ?? $deli_status->status === 'preparing')
                                                                                restaurant
                                                                            @elseif ($deli_status->status->value ?? $deli_status->status === 'delivering')
                                                                                local_shipping
                                                                            @elseif ($deli_status->status->value ?? $deli_status->status === 'arrived')
                                                                                check_circle
                                                                            @endif
                                                                        </span>
                                                                    </div>
                                                                    <div class="cds-status-label">
                                                                        {{ ucfirst(__('customer/order.' . $deli_status->status->value)) }}
                                                                        <span
                                                                            class="ms-2 small text-muted">{{ Carbon::parse($date)->translatedFormat('l, d M Y') }}</span>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                                @php $slotIdx++; @endphp
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            {{-- DESKTOP: Row of cards --}}
                            <div class="cds-delivery-slider-wrapper d-none d-lg-block">
                                <div class="cds-delivery-slider d-flex justify-content-center align-items-end">
                                    @foreach ($slots as $slotKey => $slot)
                                        @if (!empty($statusesBySlot[$slot['key']]))
                                            <div class="cds-delivery-slot-card">
                                                <div class="cds-slot-title text-center mb-2">{{ $slot['label'] }}</div>
                                                <div class="cds-slot-status-list">
                                                    @foreach ($statusesBySlot[$slot['key']] as $date => $deli_status)
                                                        <div class="cds-slot-status-row {{ $deli_status->status->value }}">
                                                            <div
                                                                class="cds-circle-icon d-flex align-items-center justify-content-center">
                                                                <span class="material-symbols-outlined status-icon-sm">
                                                                    @if ($deli_status->status->value === 'Prepared')
                                                                        restaurant
                                                                    @elseif ($deli_status->status->value === 'Delivered')
                                                                        local_shipping
                                                                    @elseif ($deli_status->status->value === 'Arrived')
                                                                        check_circle
                                                                    @endif
                                                                </span>
                                                            </div>
                                                            <div class="cds-status-label">
                                                                {{ ucfirst(__('customer/order.' . $deli_status->status->value)) }}
                                                                <span
                                                                    class="ms-2 small text-muted">{{ Carbon::parse($date)->translatedFormat('l, d M Y') }}</span>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            {{-- Order Items --}}
            <section class="card-detail-items mb-4">
                <div class="card-order" data-order-id="{{ $order->orderId }}">
                    <div class="card-header">
                        <div class="left-container">
                            <div class="text-wrapper vendor-name-wrapper">
                                <h5 class="">{{ $order->vendor->name }}</h5>
                            </div>
                            <a href="{{ route('catering-detail', $order->vendorId) }}" class="text-wrapper btn-view">
                                <p>{{__('customer/order.card_view')}}</p>
                            </a>
                        </div>
                        <div class="right-container">
                            <div class="text-wrapper order-date">
                                <p class="date">{{ Carbon::parse($order->startDate)->format('d/m/Y') }}</p>
                                <p class="date">-</p>
                                <p class="date">{{ Carbon::parse($order->endDate)->format('d/m/Y') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Redirect ke catering pagenya --}}
                    <a href="{{ route('catering-detail', $order->vendorId) }}"
                        class="card-content-wrapper text-decoration-none">
                        @foreach ($order->orderItems as $item)
                            <div class="card-content">
                                <div class="image-wrapper">
                                    {{-- <img src="{{$item->package->imgPath ? asset($item->package->imgPath) : asset('asset/catering-detail/logo-packages.png')}}" alt="Gambar Paket"> --}}
                                    <img src="{{ $item->package->imgPath ? asset('asset/menus/' . $item->package->imgPath) : asset('asset/menus/logo-packages.png') }}"
                                        alt="gambar paket">
                                </div>
                                <div class="right-container">
                                    <div class="package-detail">
                                        <div class="text-container detail-primary">{{ $item->package->name }}</div>
                                        <div class="text-container d-flex flex-row flex-md-column column-gap-2">
                                            <div class="text-wrapper detail-secondary">
                                                {{__('customer/order.card_variant')}}: {{ __('customer/order.' . $item->packageTimeSlot) }}
                                            </div>
                                            <div class="text-wrapper detail-secondary">
                                                x{{ $item->quantity }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="price-wrapper">
                                        <div class="text-wrapper">
                                            <p>
                                                Rp {{ number_format($item->price, 2, ',', '.') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </a>
                    <div class="card-bottom">
                        <div class="left-container">
                            @if ($status === 'upcoming')
                                <div class="d-flex flex-row">
                                    <button class="btn btn-danger open-cancel-modal" id="open-cancel-modal"
                                        data-order-id="{{ $order->orderId }}">
                                        {{__('customer/order.cancel')}}
                                    </button>
                                </div>
                            @endif
                        </div>
                        <div class="right-container">
                            <div class="total-container">
                                <div class="total-row d-flex justify-content-between align-items-center">
                                    <span class="total-label">{{__('customer/order.total_order')}}</span>
                                    <span class="total-value main-total">Rp
                                        {{ number_format($order->totalPrice, 2, ',', '.') }}</span>
                                </div>
                                <div class="total-row d-flex justify-content-between align-items-center">
                                    <span class="total-label">{{__('customer/order.payment_method')}}</span>
                                    <span class="total-value">{{ $paymentMethod->name }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        <!-- Manual Rate & Review Modal -->
        <div id="rateReviewModal" class="custom-modal-overlay" style="display:none;">
            <div class="custom-modal-content">
                <div class="modal-header d-flex justify-content-between align-items-center pb-1">
                    <h5 class="modal-title">{{__('customer/order.rmod_header')}}</h5>
                    <button type="button" class="btn-close" id="closeRateReviewModal" aria-label="Close">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="rating-icon-list-modal d-flex gap-1 mb-3">
                        @for ($i = 1; $i <= 5; $i++)
                            <button type="button" class="material-symbols-outlined star-icon-modal"
                                data-index="{{ $i }}">star</button>
                        @endfor
                    </div>
                    <div class="mb-3">
                        <label for="reviewText" class="form-label">{{__('customer/order.rmod_reviewheader')}}</label>
                        <textarea class="form-control" id="reviewText" rows="3" placeholder="{{__('customer/order.rmod_reviewplaceholder')}}"></textarea>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-primary" id="submitRateReviewModal">{{__('customer/order.submit')}}</button>
                    <button type="button" class="btn btn-secondary" id="cancelRateReviewModal">{{__('customer/order.cancel')}}</button>
                </div>
            </div>
        </div>

        <!-- Success Modal -->
        <div id="successModal" class="custom-modal-overlay" style="display:none;">
            <div class="custom-modal-content text-center">
                <div class="modal-header d-flex justify-content-between align-items-center pb-1">
                    <h5 class="modal-title w-100">{{__('customer/order.sucmod_header')}}</h5>
                    <button type="button" class="btn-close" id="closeSuccessModal" aria-label="Close">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <span class="material-symbols-outlined" style="font-size:48px;color:#ffc107;">star_shine</span>
                    </div>
                    <div class="mb-2">
                        <strong>{{__('customer/order.sucmod_body')}}</strong>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-primary" id="okSuccessModal">OK</button>
                </div>
            </div>
        </div>

        <!-- Modal Confirmation -->
        <div id="cancelModal" class="modal-overlay hidden">
            <div class="modal-content">
                <h4>{{__('customer/order.cancelmod_header')}}</h4>
                <p style="font-size: 16px;">{{__('customer/order.cancelmod_body')}}</p>

                <form method="POST" id="cancelForm">
                    @csrf
                    @method('put')
                    <div class="modal-actions">
                        <button type="submit" id="submitCancelOrderBtn" class="btn-confirm">{{__('customer/order.cancelmod_yes')}}</button>
                        <button type="button" id="closeModalBtn" class="btn-cancel">{{__('customer/order.cancelmod_no')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/customer/orderDetail.js') }}"></script>
@endsection
