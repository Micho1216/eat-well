<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- @vite(['resources/sass/app.scss', 'resources/js/app.js']) --}}

    <link rel="stylesheet" href="{{ asset('css/footer.css') }}">

    <link rel="stylesheet" href="{{ asset('css/navigation.css') }}">
    @yield('css')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet">
</head>

<body class="d-flex flex-column">
    <nav class="navbar navbar-expand-md custNavigation w-100">
        <div class="h-100 w-100 invisible position-absolute bg-black opacity-50 z-3 nav-visibility"></div>
        <div class="container-fluid">
            <a class="navbar-brand me-auto" href="cateringHomePage">
                <img src="/asset/navigation/eatwellLogo.png" alt="logo" style="width: 6vh;">
            </a>

            <!-- Language Dropdown -->
            {{-- <div class="dropdown dropdown-bahasa" style="margin-left: 50px">

                <button id="languageToggle" class="btn btn-outline-light dropdown-toggle" type="button"
                    data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: 20px">
                    EN
                </button>

                <ul class="dropdown-menu dropdown-bahasa">
                    <li><button class="dropdown-item" onclick="setLanguage('EN')">EN</button></li>
                    <li><button class="dropdown-item" onclick="setLanguage('ID')">ID</button></li>
                </ul>
            </div> --}}

            <form action="/lang" method="POST">
                @csrf
                <div class="dropdown-wrapper">
                    <select name="lang" id="languageSelector" style="text-align: center; margin-left: 30px;"
                        onchange="this.form.submit()">
                        <option value="en" @if (app()->getLocale() === 'en') selected @endif>EN</option>
                        <option value="id" @if (app()->getLocale() === 'id') selected @endif>ID</option>
                    </select>
                </div>
            </form>



            <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar"
                aria-labelledby="offcanvasNavbarLabel">
                <div class="offcanvas-header">
                    <img class="offcanvas-title" id="offcanvasNavbarLabel" src="/asset/navigation/eatwellLogo.png"
                        alt="logo" style="width: 10vh;">
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <ul class="navbar-nav flex-grow-1 pe-3">
                        <li class="nav-item">
                            <a class="nav-link mx-lg-2 navigationcustlink {{ Request::is('cateringHomePage') ? 'active' : '' }}"
                                href="/cateringHomePage">{{ __('vendor-nav.dashboard') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link mx-lg-2 navigationcustlink {{ Request::is('manageCateringPackage') ? 'active' : '' }}"
                                href="/manageCateringPackage">{{ __('vendor-nav.my_packages') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link mx-lg-2 navigationcustlink {{ Request::is('manageOrder') ? 'active' : '' }}"
                                href="/manageOrder">{{ __('vendor-nav.orders') }}</a>
                        </li>
                        {{-- <li class="nav-item">
                            <a class="nav-link mx-lg-2 navigationcustlink {{ Request::is('caterings') ? 'active' : '' }}"
                                href="/caterings">{{ __('vendor-nav.search') }}</a>
                        </li> --}}
                        <li class="nav-item">
                            <a class="nav-link mx-lg-2 navigationcustlink {{ Request::is('catering-detail') ? 'active' : '' }}"
                                href="{{ route('vendor.review') }}">{{ __('navigation.rating_and_review_vendor') }}</a>
                        </li>
                    </ul>

                </div>
            </div>

            {{-- <div style="padding: 0.5rem 1rem; border-radius: 0.25rem; margin-right: 2vw">
                <a class="login-button p-0" href="#">
                    <button type="button" class="login_button">Log In</button>
                </a>
            </div> --}}


            {{-- @auth
                <a href="/manage-profile">
                    <div class="imgstyle m-2" style="border-radius:100%; width:50px; height:50px; margin-right:20px;">
                        <img class="img-fluid"
                            src="{{ asset(Auth::user()->profilePath) ?? asset('asset/catering/homepage/breakfastPreview.jpg') }}"
                            alt="Vendor Logo" width="120px" style="border-radius: 100%">
                    </div>
                </a>
            @endauth --}}

            <a href="/manage-profile-vendor-account">
                <div class="imgstyle m-2" style="border-radius:100%; margin-right:20px">
                    <img class="" src="{{ asset('asset/profile/' . Auth::user()->profilePath) }}"
                        alt="Card image " width="50px" height="50px" style="border-radius: 100%">
                </div>
            </a>


            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar"
                aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </nav>

    <div class="flex-grow-1 w-100 wrapper">
        @yield('content')
    </div>

    <footer class="bg-dark text-white py-0">
        <div class="container text-center footer-page d-flex flex-column align-items-center py-4"
            style="margin-top: 10px">

            <!-- Logo + Title -->
            <div class="mb-2 text-center justify-content-center">
                <h5 class="mt-2 fw-semibold mb-0">EAT WELL</h5>
                <img src="{{ asset('asset/navigation/eatwellLogo.png') }}" alt="logo" style="width: 7vh;">
            </div>

            <!-- Navigation Links -->
            <div class="footer-links d-flex justify-content-center mb-3">
                <a href="/home" class="text-white text-decoration-none">{{ __('navigation.home') }}</a>
                <a href="/about-us" class="text-white text-decoration-none">{{ __('navigation.aboutus') }}</a>
                <a href="/contact" class="text-white text-decoration-none">{{ __('navigation.contact') }}</a>
            </div>

            <!-- Sosial Media -->
            <div class="d-flex justify-content-center gap-4 mb-2">
                <a href="https://www.facebook.com/" class="text-white fs-4"><img
                        src="{{ asset('asset/footer/1.png') }}" width="30px"></a>
                <a href="https://www.instagram.com/" class="text-white fs-4"><img
                        src="{{ asset('asset/footer/2.png') }}" width="30px"></a>
                <a href="https://www.whatsapp.com/" class="text-white fs-4"><img
                        src="{{ asset('asset/footer/3.png') }}" width="30px"></i></a>
            </div>

            <!-- Copyright -->
            <p class="text-white-50 mb-1 text-center">&copy; {{ date('Y') }} Eat Well. All rights reserved.</p>

            <!-- Alamat -->
            <p class="text-white-50 small text-center mb-0">
                Jl. Pakuan No.3, Sumur Batu, Kec. Babakan Madang, Kabupaten Bogor, Jawa Barat 16810
            </p>
        </div>
    </footer>

    @yield('scripts')
    <script src="{{ asset('js/navigation.js') }}"></script>


</body>

</html>
