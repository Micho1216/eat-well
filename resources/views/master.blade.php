<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

{{-- @php
    app()->setLocale(session()->get('lang', 'en'));
@endphp --}}

<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="{{ asset('css/footer.css') }}">

    <link rel="stylesheet" href="{{ asset('css/navigation.css') }}">
    @yield('css')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet">
</head>

<body class="d-flex flex-column min-vh-100">
    {{-- @php
        dd(__('navigation.home'));
    @endphp --}}
    <nav class="navbar navbar-expand-md custNavigation">
        <div class="h-100 w-100 invisible position-absolute bg-black opacity-50 z-3 nav-visibility"></div>
        <div class="container-fluid">
            <a class="navbar-brand me-auto" href="#">
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
                            <a class="nav-link mx-lg-2 navigationcustlink {{ Request::is('home') ? 'active' : '' }}"
                                href="/home">{{ __('navigation.home') }}</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link mx-lg-2 navigationcustlink {{ Request::is('caterings') ? 'active' : '' }}"
                                href="{{ route('search') }}">{{ __('navigation.search vendor') }}</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link mx-lg-2 navigationcustlink {{ Request::is('favorites') ? 'active' : '' }}"
                                href="{{ route('favorite.show') }}">{{ __('navigation.favorited catering') }}</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link mx-lg-2 navigationcustlink {{ Request::is('orders') ? 'active' : '' }}"
                                href="{{ route('order-history') }}">{{ __('navigation.orders') }}</a>
                        </li>


                        <li class="nav-item">
                            <a class="nav-link mx-lg-2 navigationcustlink {{ Request::is('about-us') ? 'active' : '' }}"
                                href="/about-us">{{ __('navigation.aboutus') }}</a>
                        </li>
                        {{-- <li class="nav-item">
                            <a class="nav-link mx-lg-2 navigationcustlink {{ Request::is('') ? 'active' : '' }}"
                                href="/about-us">Active Subscription</a>
                        </li> --}}



                    </ul>
                </div>
            </div>

            {{-- <div style="padding: 0.5rem 1rem; border-radius: 0.25rem; margin-right: 2vw">
                <a class="login-button p-0" href="#">
                    <button type="button" class="login_button">Log In</button>
                </a>
            </div> --}}


            @auth
                <!-- Jika sudah login -->
                {{-- <div style="padding: 0.5rem 1rem; margin-right: 2vw">
                    <a class="login-button p-0" href="/manage-profile">
                        <img src="{{ asset('asset/admin/card.png') }}" class="img-fluid" width="50px" style="border-radius: 400px">
                    </a>
                </div> --}}
                <a href="/manage-profile">
                    <div class="imgstyle m-2" style="border-radius:100%; margin-right:20px">
                        <img class="" src="{{ asset('asset/profile/' . Auth::user()->profilePath) }}"
                            alt="Card image " width="50px" height="50px" style="border-radius: 100%">
                    </div>
                </a>
            @else
                <!-- Jika belum login -->
                <div style="padding: 0.5rem 1rem; border-radius: 0.25rem; margin-right: 2vw">
                    <a class="login-button p-0" href="/login">
                        <button type="button" class="login_button">Log In</button>
                    </a>
                </div>
            @endauth


            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar"
                aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </nav>

    <div class="flex-grow-1">
        @yield('content')
    </div>

    {{-- <footer class="bg-dark text-white py-0">
        <div class="container text-center footer-page" style="margin-top: 10px; padding: 10px">

            <div class="mb-2">
                <img src="{{ asset('asset/navigation/eatwellLogo.png') }}" alt="logo" style="width: 7vh;">
                <h5 class="mt-2 fw-semibold">EAT WELL</h5>
            </div>

            <div class="mb-0">
                <a href="/home" class="text-white mx-4 text-decoration-none">Home</a>
                <a href="/about-us" class="text-white mx-4 text-decoration-none">About Us</a>
                <a href="/contact" class="text-white mx-4 text-decoration-none">Contact</a>
            </div>

            <!-- Sosial Media -->
            <div class="mb-2">
                <a href="#" class="text-white mx-3 fs-4"><i class="bi bi-facebook"></i></a>
                <a href="#" class="text-white mx-3 fs-4"><i class="bi bi-instagram"></i></a>
                <a href="#" class="text-white mx-3 fs-4"><i class="bi bi-whatsapp"></i></a>
            </div>

            <!-- Copyright -->
            <div class="mb-1">
                <p class="text-white-50 mb-1">&copy; {{ date('Y') }} Eat Well. All rights reserved.</p>
            </div>

            <!-- Alamat -->
            <div>
                <p class="text-white-50 small" style="margin-bottom: 0px">
                    Jl. Pakuan No.3, Sumur Batu, Kec. Babakan Madang, Kabupaten Bogor, Jawa Barat 16810
                </p>
            </div>

        </div>
    </footer> --}}

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
                <a href="/home" class="text-white text-decoration-none">Home</a>
                <a href="/about-us" class="text-white text-decoration-none">About Us</a>
                <a href="/contact" class="text-white text-decoration-none">Contact</a>
            </div>

            <!-- Sosial Media -->
            <div class="d-flex justify-content-center gap-4 mb-2">
                <a href="#" class="text-white fs-4"><img src="{{ asset('asset/footer/1.png') }}"
                        width="30px"></a>
                <a href="#" class="text-white fs-4"><img src="{{ asset('asset/footer/2.png') }}"
                        width="30px"></a>
                <a href="#" class="text-white fs-4"><img src="{{ asset('asset/footer/3.png') }}"
                        width="30px"></i></a>
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
