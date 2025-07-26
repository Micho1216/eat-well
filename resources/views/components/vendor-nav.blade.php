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

            {{-- <div class="dropdown-wrapper">
                <select id="languageSelector" style="text-align: center; margin-left: 30px">
                    <option value="en">EN</option>
                    <option value="id">ID</option>
                </select>
            </div> --}}

            <form action="/lang" method="post">
                @csrf
                <div class="dropdown-wrapper">
                    <select name="lang" id="languageSelector" style="text-align: center; margin-left: 30px;" onchange="this.form.submit()">
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
                <div class="offcanvas-body" style="margin-left: 5vw;">
                    <ul class="navbar-nav flex-grow-1 pe-3">
                        <li class="nav-item">
                            <a class="nav-link mx-lg-2 navigationcustlink {{ Request::is('cateringHomePage') ? 'active' : '' }}"
                                href="/cateringHomePage">{{ __('navigation.home') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link mx-lg-2 navigationcustlink {{ Request::is('manageCateringPackage') ? 'active' : '' }}"
                                href="/manageCateringPackage">{{ __('navigation.my_packages') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link mx-lg-2 navigationcustlink {{ request()->routeIs('sales.show') ? 'active' : '' }}"
                                href="{{route('sales.show')}}">Sales</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link mx-lg-2 navigationcustlink {{ Request::is('manageOrder') ? 'active' : '' }}"
                                href="/manageOrder">{{ __('navigation.orders') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link mx-lg-2 navigationcustlink {{ Request::is('ratingAndReviewVendor') ? 'active' : '' }}"
                                href="/catering-detail">{{ __('navigation.rating_and_review_vendor') }}</a>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- <div style="padding: 0.5rem 1rem; border-radius: 0.25rem; margin-right: 2vw">
                <a class="login-button p-0" href="#">
                    <button type="button" class="login_button">Log In</button>
                </a>
            </div> --}}


            @auth
                <a href="/manage-profile-vendor">
                    <div class="imgstyle m-2" style="border-radius:100%; width:50px; height:50px; margin-right:20px;">
                        <img class="img-fluid"
                            src="{{ $vendorLogo ?? asset('asset/catering/homepage/breakfastPreview.jpg') }}"
                            alt="Vendor Logo" width="120px" style="border-radius: 100%">
                    </div>
                </a>
            @endauth



            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar"
                aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </nav>

    <div class="flex-grow-1 w-100 wrapper">
        @yield('content')
    </div>

    <footer class="bg-dark text-white py-0 w-100">
        <div class="container text-center footer-page" style="margin-top: 10px; padding: 10px">

            <div class="mb-2">
                <img src="{{ asset('asset/navigation/eatwellLogo.png') }}" alt="logo" style="width: 7vh;">
                <h5 class="mt-2 fw-semibold">EAT WELL</h5>
            </div>

            <div class="mb-0">
                <a href="/home" class="text-white mx-4 text-decoration-none">{{ __('navigation.home') }}</a>
                <a href="/about-us" class="text-white mx-4 text-decoration-none">{{ __('navigation.about_us') }}</a>
                <a href="/contact" class="text-white mx-4 text-decoration-none">{{ __('navigation.contact') }}</a>
            </div>

            <!-- Sosial Media -->
            <div class="mb-2">
                <a href="#" class="text-white mx-3 fs-4"><i class="bi bi-facebook"></i></a>
                <a href="#" class="text-white mx-3 fs-4"><i class="bi bi-instagram"></i></a>
                <a href="#" class="text-white mx-3 fs-4"><i class="bi bi-whatsapp"></i></a>
            </div>

            <!-- Copyright -->
            <div class="mb-1">
                <p class="text-white-50 mb-1">&copy; {{ date('Y') }} Eat Well. {{ __('navigation.footer_rights') }}.</p>
            </div>

            <!-- Alamat -->
            <div>
                <p class="text-white-50 small" style="margin-bottom: 0px">
                   {{ __('navigation.footer_address') }}
                </p>
            </div>

        </div>
    </footer>

    @yield('scripts')
    <script src="{{ asset('js/navigation.js') }}"></script>


</body>

</html>
