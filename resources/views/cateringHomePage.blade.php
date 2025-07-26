@extends('components.vendor-nav')

@section('title', 'EatWell | Vendor Dashboard')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous" />
    <link href="https://fonts.googleapis.com/css2?family=Lexend&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #064e3b;
            /* hijau tua */
            font-family: 'Lexend', sans-serif;
            color: #f0fdf4;
        }

        .card-img-top {
            width: 100%;
            aspect-ratio: 1 / 1;
            object-fit: cover;
            border-bottom: 1px solid #ddd;
        }

        .card-title {
            font-family: 'Lexend', sans-serif;
            text-align: center;
        }

        ,

        .card-footer small {
            text-align: center;
            color: #065f46;
        }

        .card-deck {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            padding: 1rem;
            flex-wrap: wrap;
        }

        .card {
            flex: 0 0 250px;
            border: none;
            background-color: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            color: #065f46;
        }

        .heading-title {
            font-size: 30px;
            text-align: center;
            color: #ffffff;
            margin-top: 2rem;
            font-weight: 600;
        }

        .chart-container {
            background: #ffffff;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            max-width: 800px;
            margin: auto;
            color: #064e3b;
        }

        .chart-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #065f46;
            margin-bottom: 2rem;
        }

        #salesChart {
            max-height: 300px;
        }

        .btn-download {
            background-color: #10b981;
            color: white;
            font-weight: bold;
            border: none;
            padding: 12px 24px;
            border-radius: 30px;
            transition: background 0.3s;
        }

        .btn-download:hover {
            background-color: #059669;
        }

        .text-muted {
            color: #184b2a !important;
        }

        .text-muted-subheading {
            color: #ffffff !important;
        }

        .welcome-banner {
            display: flex;
            align-items: center;
            background: linear-gradient(to right, #C6E4B7, #aaf3c5);
            /* gradasi hijau muda */
            border-radius: 20px;
            padding: 5px 10px;
            max-width: 800px;
            margin: 30px auto 0 -14px;
            /* center align */
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            color: #103f0b;
            /* teks jadi hijau tua agar kontras */
        }


        .logo-circle {
            flex-shrink: 0;
            width: 150px;
            height: 150px;
            background-color: rgb(255, 255, 255);
            border-radius: 50%;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
        }

        .logo-circle img {
            width: 70%;
            height: auto;
        }

        .welcome-text h2 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .welcome-text p {
            font-size: 0.95rem;
            color: #ffffff;
            line-height: 1.4;
            text-align: justify;
        }

        @keyframes leafFall {
            0% {
                transform: translate(0, 0) rotate(0deg);
                opacity: 0;
            }

            25% {
                opacity: 0.7;
            }

            50% {
                transform: translateX(-50px) translateY(50vh) rotate(180deg);
                opacity: 1;
            }

            75% {
                transform: translateX(50px) translateY(75vh) rotate(270deg);
            }

            100% {
                transform: translateX(0) translateY(100vh) rotate(360deg);
                opacity: 0;
            }
        }

        .leaf {
            position: fixed;
            top: -100px;
            width: 60px;
            height: 60px;
            background-image: url('asset/catering/homePage/leaf.png');
            background-size: contain;
            background-repeat: no-repeat;
            opacity: 0.7;
            pointer-events: none;
            z-index: -1;
            animation-name: leafFall;
            animation-timing-function: ease-in-out;
            animation-iteration-count: infinite;
        }




        @media (max-width: 768px) {
            .chart-title {
                font-size: 1.4rem;
            }

            .chart-container {
                padding: 2rem 1rem;
            }

            .btn-download {
                width: 80%;
            }
        }

        @media (max-width: 768px) {
            @media (max-width: 768px) {
                .welcome-banner {
                    flex-direction: row;
                    align-items: flex-start;
                    text-align: left;
                    max-width: 95%;
                    padding: 15px;
                    margin: 20px 0 0 -15px;
                }

                .logo-circle {
                    width: 80px;
                    height: 80px;
                    margin-right: 10px;
                }

                .logo-circle img {
                    width: 65%;
                }

                .welcome-text h2 {
                    font-size: 1rem;
                }

                .welcome-text p {
                    font-size: 0.75rem;
                }
            }

            .leaf {
                position: fixed;
                top: -100px;
                width: 60px;
                height: 60px;
                /* background-image: url('asset/catering/homePage/leaf.png'); pastikan path-nya benar! */
                background-image: url('asset/catering/homePage/leaf.png');
                background-size: contain;
                background-repeat: no-repeat;
                opacity: 0.7;
                pointer-events: none;
                z-index: -1;
                animation-name: leafFall;
                animation-timing-function: ease-in-out;
                animation-iteration-count: infinite;
            }

            @keyframes leafFall {
                0% {
                    transform: translate(0, 0) rotate(0deg);
                    opacity: 0;
                }

                25% {
                    opacity: 0.7;
                }

                50% {
                    transform: translateX(-50px) translateY(50vh) rotate(180deg);
                    opacity: 1;
                }

                75% {
                    transform: translateX(50px) translateY(75vh) rotate(270deg);
                }

                100% {
                    transform: translateX(0) translateY(100vh) rotate(360deg);
                    opacity: 0;
                }
            }

            @media (max-width: 768px) {
                .welcome-banner {
                    flex-direction: row;
                    align-items: flex-start;
                    text-align: left;
                    max-width: 95%;
                    padding: 15px;
                    margin: 20px 0 0 -15px;
                }

                .logo-circle {
                    width: 80px;
                    height: 80px;
                    margin-right: 10px;
                }

                .logo-circle img {
                    width: 65%;
                }

                .welcome-text h2 {
                    font-size: 1rem;
                }

                .welcome-text p {
                    font-size: 0.75rem;
                }
            }

        }
    </style>
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
        /* ambil <canvas> */
        const ctx = document.getElementById('salesChart').getContext('2d');

        /* data mingguan  – datang dari controller                       */
        /* $salesData sudah berisi array 4 elemen → [week1, week2, …]    */
        const chartData = @json($salesData);

        /* build chart */
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: [
                    '{{ __('catering-home-page.week_1') }}',
                    '{{ __('catering-home-page.week_2') }}',
                    '{{ __('catering-home-page.week_3') }}',
                    '{{ __('catering-home-page.week_4') }}',
                ],
                datasets: [{
                    data: chartData, // ⬅️ pakai data dinamis
                    borderColor: '#000',
                    backgroundColor: 'transparent',
                    pointBackgroundColor: 'rgba(0,128,0,1)',
                    pointRadius: 6,
                }]
            },
            options: {
                animation: {
                    duration: 1500,
                    easing: 'easeOutQuart'
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: 'rgba(0,128,0,.7)',
                            callback: v => 'Rp' + v.toLocaleString('id-ID')
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: 'rgba(0,128,0,.7)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                },
                responsive: true,
                maintainAspectRatio: false
            }
        });
    </script>

    <script>
        const leafCount = 5;
        for (let i = 0; i < leafCount; i++) {
            const leaf = document.createElement('div');
            leaf.classList.add('leaf');
            leaf.style.left = Math.random() * 100 + 'vw';
            leaf.style.animationDuration = (8 + Math.random() * 5) + 's';
            leaf.style.animationDelay = (Math.random() * 5) + 's';
            leaf.style.transform = `translateX(${Math.random() * 100}px)`; // posisi awal random
            document.body.appendChild(leaf);
        }
    </script>
@endsection
