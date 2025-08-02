{{-- npm install chart.js --}}
{{-- import Chart from 'chart.js/auto'; --}}

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
</head>

<body>
    <x-admin-nav></x-admin-nav>

    <div style="margin: 2vw">
        <form action="{{ route('logout') }}" method="post">
            @csrf
            <button type="submit"></button>
        </form>

        <h1 class="text-center mt-3 fw-bold lexend">EAT-WELL</h1>

        <div class="lexend" style="margin: 4vw">
            <div class="row mt-5">
                <div class="col-lg-5" style="">
                    <div class="row d-flex flex-direction-column p-4"
                        style="background-color: white; border-radius: 30px; box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.2);">
                        <h4 class="mt-0 mb-3 fw-bold">{{ __('admin-dashboard.this_month_summary') }}</h4>

                        <div class="row mb-2">
                            <div class="card"
                                style="background-image: url('asset/admin/card.png'); background-size: cover; background-position: center">
                                <div class="card-body">
                                    <h5 class="card-title text-center fw-bolder mt-2 fs-4 mb-3 text-white">
                                        {{ __('admin-dashboard.profit') }}
                                    </h5>
                                    <p class="card-text text-center fs-5 text-white">Rp. {{ $profit }},00</p>
                                    <p class="card-text text-center fs-6" style="color: rgb(233, 248, 235)">
                                        {{ $percentageprofit >= 0 ? __('admin-dashboard.increased') : __('admin-dashboard.decreased') }}
                                        {{ __('admin-dashboard.by') }}
                                        {{ number_format(abs($percentageprofit), 2) }} %
                                        {{ $percentageprofit >= 0 ? '📈' : '📉' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="card"
                                style="background-image: url('asset/admin/card2.png'); background-size: cover; background-position: center">
                                <div class="card-body">
                                    <h5 class="card-title text-center fw-bolder mt-2 fs-4 mb-3 text-white">
                                        {{ __('admin-dashboard.total_sales') }}
                                    </h5>
                                    <p class="card-text text-center fs-5 text-white">Rp. {{ $totalPrice }},00</p>
                                    <p class="card-text text-center fs-6" style="color: rgb(233, 248, 235)">
                                        {{ $increment >= 0 ? __('admin-dashboard.increased') : __('admin-dashboard.decreased') }}
                                        {{ __('admin-dashboard.by') }}
                                        {{ number_format(abs($percentage), 2) }} % {{ $increment >= 0 ? '📈' : '📉' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>

                <div class="col-lg-1"></div>

                <div class="col-lg-6">
                    <canvas id="myChart" class="myChart" height="400"></canvas>
                </div>


                <div class="col-lg-1"></div>


            </div>
        </div>

        <div class="col-lg-12 table-responsive"
            style="background-color: white; border-radius: 30px; box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.2); padding:10px">
            <h1 class="fw-bold mt-3" style="margin-left: 1vw">{{ __('admin-dashboard.recent_logs') }}</h1>
            <hr>
            <table class="table table-log">
                <thead>
                    <tr>
                        <th scope="col">{{ __('admin-dashboard.no') }}</th>
                        <th scope="col">{{ __('admin-dashboard.username') }}</th>
                        <th scope="col">{{ __('admin-dashboard.role') }}</th>
                        <th scope="col">{{ __('admin-dashboard.url') }}</th>
                        <th scope="col">{{ __('admin-dashboard.desc') }}</th>
                        <th scope="col">{{ __('admin-dashboard.method') }}</th>
                        <th scope="col">{{ __('admin-dashboard.time') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($logs as $log)
                        <tr>
                            <th scope="row">{{ $loop->iteration }}</th>
                            <td>{{ $log->name }}</td>
                            <td>{{ $log->role }}</td>
                            <td>{{ $log->url }}</td>
                            <td>{{ $log->description }}</td>
                            <td>{{ $log->method }}</td>
                            <td>{{ $log->accessed_at }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="view-all-logsbtn">
                <a href="view-all-logs" class="btn btn-primary">{{ __('admin-dashboard.view_all_logs') }}</a>
            </div>
        </div>
    </div>

    <script>
        window.chartData = @json($chartData);
        window.labels = @json($labels);
    </script>

    <script src="{{ asset('js/admin/dashboard.js') }}"></script>

    <x-admin-footer></x-admin-footer>
</body>

</html>
