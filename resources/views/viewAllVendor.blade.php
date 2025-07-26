<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>View all vendors</title>
    <link rel="stylesheet" href="{{ asset('css/admin/viewall.css') }}">
</head>

<body >
    <x-admin-nav></x-admin-nav>

    <h1 class="text-center mt-5 fw-semibold lexend">{{ __('view-all-vendor.all_vendor') }}</h1>

    <div class="row w-100 lexend">
        <div class="col-1 p-0">

        </div>
        <div class="col-10 p--0">
            <form action="/view-all-vendors" method="POST" class="d-flex jusify-content-center align-items-center"
                role="search">
                @csrf
                @method('post')
                <input class="form-control me-2" type="search" placeholder="{{ __('view-all-vendor.search_vendor_name') }}" aria-label="Search"
                    name="name" />
                <button class="btn btn-outline-success" type="submit">{{ __('view-all-vendor.search') }}</button>
            </form>
        </div>
        <div class="col-1 p-0">

        </div>

    </div>


    <div class="container mt-3 mb-3">
        <div class="row d-flex justify-content-center align-items-center">

            @if ($vendors->isEmpty())
                <div class="text-center mt-5" style="min-height: 100vh">
                    <h4>{{ __('view-all-vendor.vendor_not_found') }}</h4>
                </div>
            @else
                @foreach ($vendors as $vendor)
                    <div class="card col-md-3 mt-3 mx-3" style="width: 18rem; min-height: 20vh">
                        <div class="imgstyle m-2"
                            style="background-color:black; border-radius:100%; width:100px; height:100px">
                            <img class="card-img-top" src="{{ asset('asset/catering/homepage/breakfastPreview.jpg') }}"
                                alt="Card image cap" width="120px" style="border-radius: 100%">
                        </div>

                        <hr>

                        <div class="card-body">
                            <h4 class="card-title lexend">{{ $vendor->name }}</h4>
                            <p class="card-text">Profit : Rp. {{ number_format($sales[$vendor->vendorId] ?? 0, 0, ',', '.') }},00</p>
                            <p class="card-text">✆ : {{ $vendor->phone_number }}</p>
                            <p class="card-text">🏠︎ : {{ $vendor->jalan . ', ' . $vendor->kota }}</p>
                            
                        </div>
                    </div>
                @endforeach
            @endif



        </div>
    </div>

    <x-admin-footer></x-admin-footer>

</body>

</html>
