<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('view-all-vendor.page_title') }}</title>
    <link rel="stylesheet" href="{{ asset('css/admin/viewall.css') }}">
</head>

<body>
    <x-admin-nav />

    <h1 class="text-center mt-5 fw-semibold lexend">{{ __('view-all-vendor.all_vendor') }}</h1>

    <div class="row w-100 lexend my-4">
        <div class="col-1"></div>

        <div class="col-10">
            <form action="{{ url('/view-all-vendors') }}" method="POST" class="d-flex justify-content-center align-items-center" role="search">
                @csrf
                <input class="form-control me-2" type="search" name="name" placeholder="{{ __('view-all-vendor.search_vendor_name') }}" aria-label="Search" />
                <button class="btn btn-outline-success" type="submit">{{ __('view-all-vendor.search') }}</button>
            </form>
        </div>

        <div class="col-1"></div>
    </div>

    <div class="container my-4">
        <div class="row d-flex justify-content-center align-items-start flex-wrap">
            @if ($vendors->isEmpty())
                <div class="text-center mt-5" style="min-height: 100vh;">
                    <h4>{{ __('view-all-vendor.vendor_not_found') }}</h4>
                </div>
            @else
                @foreach ($vendors as $vendor)
                    <div class="card col-md-3 m-3 p-0" style="width: 18rem; min-height: 20vh;">
                        <div class="d-flex justify-content-center mt-3">
                            <div class="imgstyle" style="background-color: black; border-radius: 50%; width: 100px; height: 100px; overflow: hidden;">

                                @if ($vendor->logo != null)
                                    <img class="card-img-top" src="{{ asset('asset/vendorLogo/' . $vendor->logo) }}" alt="Vendor Logo" width="100" height="100" style="object-fit: cover; border-radius: 50%;">
                                @endif
    
                            </div>
                        </div>

                        <hr class="mx-3">

                        <div class="card-body text-center">
                            <h4 class="card-title lexend">{{ $vendor->name }}</h4>
                            <p class="card-text">Profit: Rp {{ number_format($sales[$vendor->vendorId] ?? 0, 0, ',', '.') }},00</p>
                            <p class="card-text">✆: {{ $vendor->phone_number }}</p>
                            <p class="card-text">🏠: {{ $vendor->jalan }}, {{ $vendor->kota }}</p>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <x-admin-footer />
</body>
</html>
