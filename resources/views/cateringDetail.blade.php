@extends('master')

@section('title')
    {{ $vendor->name }}
@endsection

@section('css')
    <meta name="vendor-id" content="{{ $vendor->vendorId }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- CSS --}}
    <link rel="stylesheet" href="{{ asset('css/cateringDetail.css') }}">
    {{-- bootstrap --}}
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:FILL@1" rel="stylesheet" />
@endsection

@section('content')
    <div class="profile-container">
        <div class="container daun-container">
            <img src="{{ asset('asset/catering-detail/daun1.png') }}" alt="Catering Image" class="daun1">
            <img src="{{ asset('asset/catering-detail/daun2.png') }}" alt="Catering Image" class="daun2">
            <div class="container all-profile-wrapper">
                <div class="catering-info-left-wrapper">
                    <h1 class="lexend">{{ $vendor->name }}</h1>
                    {{-- <p class="inter sold-text">10k+ sold</p> --}}

                    <div class="phone-number-and-schedule-wrapper">
                        <div class="phone-number-container">
                            <span class="material-symbols-outlined call-icon">call</span>
                            <span class="inter phone-number">{{ $vendor->phone_number }}</span>
                        </div>
                        <div class="schedule-container">
                            {{-- <span class="inter schedule">Monday - Sunday</span> --}}
                            <span class="inter schedule">{{ $numSold }} {{ __('catering-detail.sold') }}</span>
                        </div>
                    </div>

                    <div class="location-wrapper">
                        <span class="material-symbols-outlined location-icon">pin_drop</span>
                        <span class="inter address">{{ $vendor->jalan }}, {{ $vendor->kelurahan }},
                            {{ $vendor->kecamatan }}, {{ $vendor->kota }}, {{ $vendor->provinsi }},
                            {{ $vendor->kode_pos }}</span>
                    </div>

                    <div class="rating-and-number-sold-wrapper">
                        <a href="{{ route('rate-and-review', $vendor->vendorId) }}" class="rate-review-button">
                            <div class="rating-container">
                                <span class="material-symbols-outlined star-icon">star</span>
                                @if ($vendor->rating > 0)
                                    <span class="inter rating-and-sold">{{ $vendor->rating }}</span>
                                    <span class="inter rating-and-sold">{{ $vendor->rating }}</span>
                                @endif
                            </div>
                        </a>
                        <div class="number-sold-container">
                            {{-- <span class="inter rating-and-sold">10k+</span>
                            <span class="inter sold-text">sold</span> --}}
                            <span class="inter rating-and-sold">{{ __('catering-detail.order') }}</span>
                        </div>
                    </div>
                </div>

                <div class="catering-info-right-wrapper">
                    <div class="hijau-luar">
                        <div class="cokelat-lingkaran">
                            <div>
                                {{-- <img src="{{ asset('asset/catering-detail/logo-aldenaire-catering.jpg') }}" alt="Catering Image" class="logo-catering"> --}}
                                <img src="{{ asset('asset/vendorLogo/' . $vendor->logo) }}" alt="Catering Image" class="logo-catering">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="food-preview-container">
        <h1 class="lexend">{{ __('catering-detail.food_preview') }}</h1>

        <div class="carousel-wrapper">
            <div id="carouselExampleAutoplaying" class="carousel slide" data-bs-ride="carousel" data-bs-touch="true"
                data-bs-interval="2500">
                <div class="carousel-indicators">
                    @foreach ($vendor->previews as $key => $preview)
                        <button type="button" data-bs-target="#carouselExampleAutoplaying"
                            data-bs-slide-to="{{ $key }}" class="{{ $loop->first ? 'active' : '' }}"
                            aria-current="{{ $loop->first ? 'true' : 'false' }}"
                            aria-label="Slide {{ $key + 1 }}"></button>
                    @endforeach
                </div>
                <div class="carousel-inner">
                    @foreach ($vendor->previews as $key => $preview)
                        <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                            <img src="{{ asset('asset/catering-preview/' . $preview->previewPicturePath) }}" class="d-block w-100"
                                alt="Food Preview {{ $key + 1 }}">
                        </div>
                    @endforeach
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleAutoplaying"
                    data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleAutoplaying"
                    data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>
    </div>
    </div>

    <div class="price-and-shipping-container">
        <div class="weekly-price-wrapper">
            <h1 class="lexend">{{ __('catering-detail.weekly_price') }}</h1>
            <div class="price-container">
                <div class="price-bulet">
                    {{-- <h1 class="lexend">625k</h1> --}}
                    <h1 class="lexend" id="displayedPrice"></h1>
                </div>
                <div class="price-kanan">
                    <div class="dropdown">
                        <button id="dropdownMenuButton" class="btn btn-secondary dropdown-toggle inter" type="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            {{ $packages[0]->name }}
                        </button>
                        <ul class="dropdown-menu">
                            @foreach ($packages as $package)
                                {{-- <li class="dropdown-item inter">{{ $package->name }}</li> --}}
                                <li class="dropdown-item inter" data-package-id="{{ $package->packageId }}"
                                    data-breakfast-price="{{ $package->breakfastPrice ?? 'null' }}"
                                    data-lunch-price="{{ $package->lunchPrice ?? 'null' }}"
                                    data-dinner-price="{{ $package->dinnerPrice ?? 'null' }}">
                                    {{ $package->name }}
                                </li>
                            @endforeach
                            {{-- <li class="dropdown-item inter">Package A</li>
                            <li class="dropdown-item inter">Package B</li>
                            <li class="dropdown-item inter">Package C</li> --}}
                        </ul>
                    </div>

                    <!-- Hidden input to hold selected value for form submission -->
                    <input type="hidden" name="selected_package" id="selectedPackage" value="Package A">

                    <ul class="list-group inter" id="mealOptions">
                        {{-- These will be dynamically populated by JavaScript --}}
                    </ul>
                </div>
            </div>
        </div>
        <div class="shipping-wrapper">
            <h1 class="lexend text-center">{{ __('catering-detail.shipping_time') }}</h1>
            <p class="inter text-white schedule-dipake">{{ __('catering-detail.monday-sunday') }}</p>
            @if ($vendor->breakfast_delivery)
                <div class="section-makan">
                    <h3 class="inter">{{ __('catering-detail.breakfast') }}</h3>
                    <p class="inter">{{ $vendor->breakfast_delivery }}</p>
                </div>
            @endif
            @if ($vendor->lunch_delivery)
                <div class="section-makan">
                    <h3 class="inter">{{ __('catering-detail.lunch') }}</h3>
                    <p class="inter">{{ $vendor->lunch_delivery }}</p>
                </div>
            @endif
            @if ($vendor->dinner_delivery)
                <div class="section-makan">
                    <h3 class="inter">{{ __('catering-detail.dinner') }}</h3>
                    <p class="inter">{{ $vendor->dinner_delivery }}</p>
                </div>
            @endif
        </div>
    </div>

    <section id="packages">
        <div class="container packages">
            <h1 class="lexend">{{ __('catering-detail.our-packages') }}</h1>
            <div class="container">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="accordions inter">
                            @foreach ($packages as $package)
                                <div class="accordion-item">
                                    <div class="accordion-title" data-package-id="{{ $package->packageId }}">
                                        <div class="left-card-wrapper">
                                            @if ($package->imgPath)
                                                <div>
                                                    <img src="{{ asset('asset/menus/' . $package->imgPath) }}"
                                                        alt="{{ $package->name }} Image" class="package-image">
                                                </div>
                                            @else
                                                <div>
                                                    <img src="{{ asset('asset/menus/logo-packages.png') }}"
                                                        alt="Packages Image" class="package-image">
                                                </div>
                                            @endif
                                            <div>
                                                <div class="nama-package-dan-download-wrapper">
                                                    <h4>{{ $package->name }}</h4>
                                                    {{-- <div class="download-wrapper">
                                                        <span class="material-symbols-outlined download-icon"
                                                            data-pdf="{{ asset($package->menuPDFPath) }}">
                                                            download
                                                        </span>
                                                    </div> --}}
                                                    @if ($package->menuPDFPath)
                                                        {{-- <div class="download-wrapper" data-pdf="{{ asset('asset/menus/' . $package->menuPDFPath) }}">
                                                            <span class="material-symbols-outlined download-icon ms-1">
                                                                download
                                                            </span>
                                                            <span class="me-2 inter">{{ __('catering-detail.download_menu') }}</span>
                                                        </div> --}}
                                                        <div class="download-wrapper ms-2" data-pdf="{{ asset('asset/menus/' . $package->menuPDFPath) }}">
                                                            <span class="material-symbols-outlined download-icon">
                                                                download
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="category-cuisine-wrapper">
                                                    <span
                                                        class="category-cuisine-bold">{{ __('catering-detail.category') }}:</span>
                                                    <span>{{ $package->category->categoryName ?? 'N/A' }}</span>
                                                    <div></div>
                                                    <span
                                                        {{-- class="category-cuisine-bold">{{ __('catering-detail.cuisine_type') }}:</span> --}}
                                                        class="category-cuisine-bold">{{ __('catering-detail.avg_calories') }}:</span>
                                                    <span>
                                                        {{-- @forelse ($package->cuisineTypes as $cuisine)
                                                            {{ $cuisine->cuisineName }}{{ !$loop->last ? ', ' : '' }}
                                                        @empty
                                                            N/A
                                                        @endforelse --}}
                                                        ± {{ $package->averageCalories }} {{ __('catering-detail.kkal') }}
                                                    </span>
                                                    {{-- @if ($package->menuPDFPath)
                                                        <div class="download-wrapper" data-pdf="{{ asset('asset/menus/' . $package->menuPDFPath) }}">
                                                            <span class="material-symbols-outlined download-icon ms-1">
                                                                download
                                                            </span>
                                                            <span class="me-2 inter">{{ __('catering-detail.download_menu') }}</span>
                                                        </div>
                                                    @endif --}}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="right-card-wrapper">
                                            @if ($package->menuPDFPath)
                                                {{-- <p class="view-menu-text inter" data-pdf="{{ asset('asset/catering-detail/pdf/vegetarian-package-menu.pdf') }}"> --}}
                                                <p class="view-menu-text inter"
                                                    data-pdf="{{ asset('asset/menus/' . $package->menuPDFPath) }}">
                                                    {{ __('catering-detail.view_menu') }}
                                                </p>
                                            @endif
                                            <div class="add-button" data-tab="item{{ $package->packageId }}">
                                                <p class="add-text inter">{{ __('catering-detail.add') }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="accordion-content" id="item{{ $package->packageId }}">
                                        <div class="menu-item">
                                            @if (!is_null($package->breakfastPrice))
                                                <div class="item-row">
                                                    <span data-meal-type="breakfast">{{ __('catering-detail.breakfast') }}</span>
                                                    <span class="price" data-price="{{ $package->breakfastPrice }}">Rp
                                                        {{ number_format($package->breakfastPrice, 0, ',', '.') }}</span>
                                                    <div class="qty-control">
                                                        <button class="decrement">−</button>
                                                        <span class="qty">0</span>
                                                        <button class="increment">+</button>
                                                    </div>
                                                </div>
                                            @endif

                                            @if (!is_null($package->lunchPrice))
                                                <div class="item-row">
                                                    <span data-meal-type="lunch">{{ __('catering-detail.lunch') }}</span>
                                                    <span class="price" data-price="{{ $package->lunchPrice }}">Rp
                                                        {{ number_format($package->lunchPrice, 0, ',', '.') }}</span>
                                                    <div class="qty-control">
                                                        <button class="decrement">−</button>
                                                        <span class="qty">0</span>
                                                        <button class="increment">+</button>
                                                    </div>
                                                </div>
                                            @endif

                                            @if (!is_null($package->dinnerPrice))
                                                <div class="item-row">
                                                    <span data-meal-type="dinner">{{ __('catering-detail.dinner') }}</span>
                                                    <span class="price" data-price="{{ $package->dinnerPrice }}">Rp
                                                        {{ number_format($package->dinnerPrice, 0, ',', '.') }}</span>
                                                    <div class="qty-control">
                                                        <button class="decrement">−</button>
                                                        <span class="qty">0</span>
                                                        <button class="increment">+</button>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- <div class="button-order inter" id="proceedToPaymentButton">
            <span class="order-message">No Package Selected Yet.</span>
            <span class="package-count" style="display:none;"></span>
            <span class="item-count" style="display:none;"></span>
            <span class="price-total" style="display:none;"></span>
        </div> --}}

        {{-- <div style="background-color: #eee; padding: 10px; margin-top: 20px;">
            <p>Debug: Selected Address ID = {{ $selectedAddress->addressId ?? 'N/A' }}</p>
            <p>Debug: Selected Address Jalan = {{ $selectedAddress->jalan ?? 'N/A' }}</p>
        </div> --}}

        <a href="{{ route('payment.show', ['vendor' => $vendor->vendorId, 'address_id' => $selectedAddress->addressId]) }}" class="button-order inter"
            id="proceedToPaymentLink" style="cursor: default; pointer-events: none; text-decoration: none;">
            <span class="order-message">No Package Selected Yet.</span>
            <span class="package-count" style="display:none;"></span>
            <span class="item-count" style="display:none;"></span>
            <span class="price-total" style="display:none;"></span>
        </a>
    </section>

    <!-- Modal PDF Viewer -->
    <div id="pdfModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <iframe id="pdfFrame" src="" width="100%" height="600px" frameborder="0"></iframe>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('js/catering detail/modalPdf-cateDetail.js') }}"></script>
    <script src="{{ asset('js/catering detail/dropdown-cateDetail.js') }}"></script>
    <script src="{{ asset('js/catering detail/package-cateDetail.js') }}"></script>
@endsection
