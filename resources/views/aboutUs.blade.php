@extends('master')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
<link rel="stylesheet" href="{{ asset('css/aboutUs.css') }}">
@endsection

@section('content')
<section class="about-us py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title text-uppercase">{{ __('about-us.about_us') }}</h2>
            <p class="text-light">{{ __('about-us.tagline') }}</p>
        </div>

        <div class="row align-items-center mb-5">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <img src="{{ asset('asset/landing_page/2.png') }}" alt="About Eat Well" class="img-fluid rounded shadow" style="background-color: #185640">
            </div>
            <div class="col-lg-6" style="background-color: #185640; border-radius: 20px; padding: 2rem;">
                <h3 class="mb-3 text-light">{{ __('about-us.our_mission_title') }}</h3>
                <p>{{ __('about-us.our_mission_p1') }}</p>
                <p>{{ __('about-us.our_mission_p2') }}</p>
            </div>
        </div>

        <div class="row align-items-center flex-lg-row-reverse">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h1>{{ __('about-us.team_title') }}</h1>
                <img src="{{ asset('asset/aboutUs/ewt.png') }}" alt="Team" class="img-fluid shadow" style="background-color: #CFE6B0; border-radius: 30px;">
            </div>
            <div class="col-lg-6">
                <h3 class="mb-3 text-warning">{{ __('about-us.why_choose_title') }}</h3>
                <ul class="list-unstyled">
                    <li class="mb-2">{{ __('about-us.reason1') }}</li>
                    <li class="mb-2">{{ __('about-us.reason2') }}</li>
                    <li class="mb-2">{{ __('about-us.reason3') }}</li>
                    <li class="mb-2">{{ __('about-us.reason4') }}</li>
                </ul>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
</script>
@endsection
