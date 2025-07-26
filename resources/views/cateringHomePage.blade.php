@extends('components.vendor-nav')

@section('title', 'EatWell | Vendor Dashboard')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous" />
    <link href="https://fonts.googleapis.com/css2?family=Lexend&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/cateringHomePage.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous">
    </script>
@endsection

@section('content')
    {{-- Untuk button ini jangan dihapus, untuk sementara button logout disini, menunggu UI logout beneran dibuat --}}
    <form action="{{ route('logout') }}" method="post">
        @csrf
        <button type="submit"></button>
    </form>
    <div class="welcome-banner">
        <div class="logo-circle">
            <img src="asset/catering/homePage/logoCatering.png" alt="Logo" />
        </div>
        <div class="welcome-text">
            <h2>{{ __('catering-home-page.welcome') }}, {{ $vendor->name }}!</h2>
            <p style="text-align: justify; color:black;">
                {{ __('catering-home-page.intro') }}
            </p>
        </div>
    </div>
    <div class="heading-title">{{ __('catering-home-page.analyze_sales') }}</div>
    <div class="text-muted-subheading text-center" style="font-family: 'Roboto', sans-serif;">
        {{ __('catering-home-page.download_report_desc') }}</div>

    <div class="container my-5">
        <div class="chart-container text-center">
            <h2 class="chart-title">{{ __('catering-home-page.income_statistics') }} {{ $salesMonth }} </h2>
            <canvas id="salesChart"></canvas>
            <button class="btn-download mt-4">{{ __('catering-home-page.download_report') }}</button>
        </div>
    </div>

    <p class="heading-title">{{ __('catering-home-page.todays_orders') }}</p>
    @php
        $slotMeta = [
            'breakfast' => [
                'title' => __('catering-home-page.breakfast'),
                'img' => asset('asset/catering/homePage/breakfastPreview.png'),
                'time' => $vendor->breakfast_delivery ?? '-',
            ],
            'lunch' => [
                'title' => __('catering-home-page.lunch'),
                'img' => asset('asset/catering/homePage/lunchPreview.png'),
                'time' => $vendor->lunch_delivery ?? '-',
            ],
            'dinner' => [
                'title' => __('catering-home-page.dinner'),
                'img' => asset('asset/catering/homePage/dinnerPreview.png'),
                'time' => $vendor->dinner_delivery ?? '-',
            ],
        ];
    @endphp



    <div class="card-deck">
        @foreach ($slotMeta as $slotKey => $meta)
            <div class="card">
                <img class="card-img-top" src="{{ $meta['img'] }}" alt="{{ $meta['title'] }} Preview" />

                <div class="card-body">
                    <h5 class="card-title">{{ $meta['title'] }}</h5>

                    {{-- daftar paket & qty --}}
                    @forelse ($slotCounts[$slotKey] ?? [] as $pkg => $qty)
                        <p class="card-text m-0" style="text-align: left">
                            {{ $qty }} × {{ $pkg }}
                        </p>
                    @empty
                        <p class="card-text text-muted">{{ __('catering-home-page.no_orders') }}</p>
                    @endforelse
                </div>

                <div class="card-footer">
                    <small class="text-muted">{{ __('catering-home-page.served_from') }} {{ $meta['time'] }}</small>
                </div>
            </div>
        @endforeach
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        window.chartData = @json($salesData);
        window.locale = {
            week_1: '{{ __('catering-home-page.week_1') }}',
            week_2: '{{ __('catering-home-page.week_2') }}',
            week_3: '{{ __('catering-home-page.week_3') }}',
            week_4: '{{ __('catering-home-page.week_4') }}',
        };
    </script>

    <script src="{{ asset('js/vendor/cateringHomePage.js') }}"></script>


@endsection
