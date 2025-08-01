<footer class="bg-dark text-white py-0">
    <div class="container text-center footer-page d-flex flex-column align-items-center py-4" style="margin-top: 10px">

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
            <a href="https://www.facebook.com/" class="text-white fs-4"><img src="{{ asset('asset/footer/1.png') }}"
                    width="30px"></a>
            <a href="https://www.instagram.com/" class="text-white fs-4"><img src="{{ asset('asset/footer/2.png') }}"
                    width="30px"></a>
            <a href="https://www.whatsapp.com/" class="text-white fs-4"><img src="{{ asset('asset/footer/3.png') }}"
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
