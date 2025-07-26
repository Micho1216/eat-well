@extends('master')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('css/landingPage.css') }}">
@endsection

@section('content')
    {{-- <div class="container mainPage">
        <div class="landingPagesDesc">
            <div class="landingpageText">
                <h1>EAT WELL</h1>

                <p>Eat Well is a platform that connects you with the best catering services in your area, offering a wide
                    range of delicious and healthy meal options for any occasion.</p>

                <button class="registerButtonLandingPage">
                    <a href="/login" class="text-white textButtonLandingPage text-decoration-none">Register now</a>
                    <div class="buttonpanah"><img src="{{ asset('asset/landing_page/panah.png') }}" alt="panah"
                            width="80px"></div>
                </button>
            </div>

            <div class="mealImageLandingPage">
                <img src="{{ asset('asset/landing_page/gambarMakanan.png') }}" alt="Meal Image" width="500px">
            </div>
        </div>

        <div class="ourClient">
            <div class="backgrounBox">
                <div class="titlebox">
                    <div class="client1">
                        <div class="clientBox">
                            <div class="clientProfile">
                                <img src="" alt="profile">
                            </div>
                            <div class="clientDesc">

                            </div>
                            <div class="clientName">

                            </div>
                        </div>
                    </div>
                    <div class="client2">
                        <div class="clientBox">
                            <div class="clientProfile">

                            </div>
                            <div class="clientDesc">

                            </div>
                            <div class="clientName">

                            </div>
                        </div>
                    </div>
                    <div class="client3">
                        <div class="clientBox">
                            <div class="clientProfile">

                            </div>
                            <div class="clientDesc">

                            </div>
                            <div class="clientName">

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}

    <div class="container">
        <div class="row landingpageText justify-content-between" style="margin-top: 20px">
            <div class="col-sm-5">
                <h1>EAT WELL</h1>

                <p>{{ __('landing-page.eatwellis') }}</p>

                <button type="button" class="btn btn-outline-warning registerButtonLandingPage" style="margin-top: 30px">
                    <a href="/register"
                        class="text-white textButtonLandingPage text-decoration-none fw-bold">{{ __('landing-page.registernow') }}</a>
                    <div class="buttonpanah"><img src="{{ asset('asset/landing_page/panah.png') }}" alt="panah"
                            class="img-fluid" width="50px"></div>
                </button>
            </div>
            <div class="col-sm-6 offset-1 mealImageLandingPage">
                <img src="{{ asset('asset/landing_page/gambarMakanan.png') }}" alt="Meal Image" class="img-fluid">
            </div>
        </div>

        {{-- <div class="row">
        <div class="row kotakHijau">
            <div class="col-sm-2 kotakPertama">
                <img src="{{ asset('asset/landing_page/2.png') }}" alt="Our Client" class="img-fluid" width="130px">
            </div>
            <div class="col-sm-3 kotakKedua">
                <img src="{{ asset('asset/landing_page/2.png') }}" alt="Our Client" class="img-fluid" width="130px">
            </div>
            <div class="col-sm-3 kotakKedua">
                <img src="{{ asset('asset/landing_page/2.png') }}" alt="Our Client" class="img-fluid" width="130px">
            </div>
            <div class="col-sm-3 kotakKedua">
                <img src="{{ asset('asset/landing_page/2.png') }}" alt="Our Client" class="img-fluid" width="130px">
            </div>

       </div> --}}

        <div class="row info-section text-white rounded-4 overflow-hidden">
            <!-- Kotak Kuning -->
            <div class="col-2 d-flex align-items-center justify-content-center" style="background-color: #FDB935;">
                <img src="{{ asset('asset/landing_page/2.png') }}" alt="Our Client" width="130px"
                    class="gambarKotakKUning">
            </div>

            <!-- Kolom Teks 1 -->
            <div class="col-3 d-flex align-items-center justify-content-center text-center p-3">
                <p class="mb-0">{{ __('landing-page.kolomtext1') }}</p>
            </div>

            <!-- Divider -->
            <div class="col-auto d-flex align-items-center justify-content-center divider">
                <div style="border-left: 1px solid #ccc; height: 60px;"></div>
            </div>

            <!-- Kolom Teks 2 -->
            <div class="col-3 d-flex align-items-center justify-content-center text-center p-3">
                <p class="mb-0">{{ __('landing-page.kolomtext2') }}</p>
            </div>

            <!-- Divider -->
            <div class="col-auto d-flex align-items-center justify-content-center divider">
                <div style="border-left: 1px solid #ccc; height: 60px;"></div>
            </div>

            <!-- Kolom Teks 3 -->
            <div class="col-2 d-flex align-items-center justify-content-center text-center p-3">
                <p class="mb-0">{{ __('landing-page.kolomtext3') }}</p>
            </div>
        </div>

        {{-- <div class="h1Client">
            <h1 class="text-center mt-5 fw-bold text-light">Our Clients</h1>
        </div>
        <div class="row clientList justify-content-center mt-4 text-center">
            <div class="col-sm-2">
                <div class="clientBox">

                </div>
            </div>
            <div class="col-sm-2">
                Client 2
            </div>
            <div class="col-sm-2">
                Client 3
            </div>
        </div> --}}

        <div class="container-fluid py-5"
            style="background: linear-gradient(to right, #2c5742, #9ccf9b); border-radius: 20px; margin-top: 50px;">
            <div class="text-center mb-5">
                <span class="px-4 py-2 text-white rounded-pill fs-5 our-clients">{{ __('landing-page.ourclient') }}</span>
            </div>

            <div class="row justify-content-center position-relative">
                <!-- Card 1 -->
                <div class="col-md-4 mb-4">
                    <div class="bg-white rounded-4 p-4 text-center position-relative shadow" style="overflow: hidden;">
                        <img src="{{ asset('asset/landing_page/ivan.jpg') }}" class="rounded-circle"
                            style="width:100px; height:100px;">
                        <p class="mt-5 text-muted small px-2">
                            <i class="bi bi-quote"></i>
                            {{ __('landing-page.client_desc') }}
                            <i class="bi bi-quote"></i>
                        </p>
                        <div class="bg-warning text-white fw-bold rounded-pill py-1 px-3 d-inline-block mt-3">
                            Ivan Cornelius S
                        </div>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="col-md-4 mb-4">
                    <div class="bg-white rounded-4 p-4 text-center position-relative shadow" style="overflow: hidden;">
                        <img src="{{ asset('asset/landing_page/ivan.jpg') }}" class="rounded-circle"
                            style="width:100px; height:100px;">
                        <p class="mt-5 text-muted small px-2">
                            <i class="bi bi-quote"></i>
                            {{ __('landing-page.client_desc') }}
                            <i class="bi bi-quote"></i>
                        </p>
                        <div class="bg-warning text-white fw-bold rounded-pill py-1 px-3 d-inline-block mt-3">
                            Ivan Cornelius S
                        </div>
                    </div>
                </div>

                <!-- Card 3 + garis penghubung -->
                <div class="col-md-4 mb-4 position-relative">
                    <div class="bg-white rounded-4 p-4 text-center position-relative shadow" style="overflow: hidden;">
                        <img src="{{ asset('asset/landing_page/ivan.jpg') }}" class="rounded-circle"
                            style="width:100px; height:100px;">
                        <p class="mt-5 text-muted small px-2">
                            <i class="bi bi-quote"></i>
                            {{ __('landing-page.client_desc') }}
                            <i class="bi bi-quote"></i>
                        </p>
                        <div class="bg-warning text-white fw-bold rounded-pill py-1 px-3 d-inline-block mt-3">
                            Ivan Cornelius S
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <h1 class="text-center mt-5 bold text-light">{{ __('landing-page.Why Choose Us') }}</h1>
        <p class="text-center fw-normal text-light" style="margin-top: 30px">{{ __('landing-page.reason') }}</p>
        <div class="row justify-content-center" style="margin-bottom: 40px">
            <div class="row justify-content-center text-center mt-5">
                <div class="col-4 col-sm-4 mb-4">
                    <img src="{{ asset('asset/landing_page/eat1.png') }}" class="rounded-circle img-fluid mx-auto d-block"
                        style="width: 100px; height: 100px;">
                    <p class="fw-light text-light mt-2">{{ __('landing-page.reason1') }}</p>
                </div>

                <div class="col-4 col-sm-4 mb-4">
                    <img src="{{ asset('asset/landing_page/eat2.png') }}" class="rounded-circle img-fluid mx-auto d-block"
                        style="width: 100px; height: 100px;">
                    <p class="fw-light text-light mt-2">{{ __('landing-page.reason2') }}</p>
                </div>

                <div class="col-4 col-sm-4 mb-4">
                    <img src="{{ asset('asset/landing_page/eat3.png') }}" class="rounded-circle img-fluid mx-auto d-block"
                        style="width: 100px; height: 100px;">
                    <p class="fw-light text-light mt-2">{{ __('landing-page.reason3') }}</p>
                </div>
            </div>

        </div>






    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>
@endsection
