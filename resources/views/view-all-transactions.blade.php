<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ __('view-all-transactions.title') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <x-admin-nav></x-admin-nav>

    <div class="container-fluid">

        <h1 class="lexend fw-bold text-center mt-5 mb-5">{{ __('view-all-transactions.heading') }}</h1>
        <hr>

        <div class="" style="color: red;">
            {{ session()->get('message_del', '') }}
        </div>

        <div class="" style="color: rgb(15, 157, 24);">
            {{ session()->get('message_add', '') }}
        </div>

        @if ($payments->isEmpty())
            <h3 class="text-center fw-bold lexend mt-5" style="margin-bottom: 130px">{{ __('view-all-transactions.no_data') }}</h3>
        @else
        <div class="table-responsive">
            <table class="table text-center" style="margin-bottom: 130px">
                <thead>
                    <tr>
                        <th scope="col" style="background-color: rgb(165, 203, 165) !important">{{ __('view-all-transactions.no') }}</th>
                        <th scope="col" style="background-color: rgb(165, 203, 165) !important">{{ __('view-all-transactions.id') }}</th>
                        <th scope="col" style="background-color: rgb(165, 203, 165) !important">{{ __('view-all-transactions.payment_method') }}</th>
                        <th scope="col" style="background-color: rgb(165, 203, 165) !important">{{ __('view-all-transactions.customer') }}</th>
                        <th scope="col" style="background-color: rgb(165, 203, 165) !important">{{ __('view-all-transactions.vendor') }}</th>
                        <th scope="col" style="background-color: rgb(165, 203, 165) !important">{{ __('view-all-transactions.total_price') }}</th>
                        <th scope="col" style="background-color: rgb(165, 203, 165) !important">{{ __('view-all-transactions.paid_at') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($payments as $payment)
                        <tr>
                            <th scope="row">{{ $loop->iteration }}</th>
                            <td>{{ $payment->paymentId }}</td>
                            <td>{{ $payment->paymentMethod->name }}</td>
                            <td>{{ $payment->order->user->name }}</td>
                            <td>{{ $payment->order->vendor->name }}</td>
                            <td>{{ 'Rp' . number_format($payment->order->orderItems->sum('price'), 2, ',', '.') }}</td>
                            <td>{{ $payment->paid_at }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($payments->lastPage() > 1)
            <ul class="catering-pagination pagination justify-content-center my-3">
                <li class="page-item {{ $payments->onFirstPage() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $payments->previousPageUrl() ?? '#' }}" tabindex="-1">&laquo;</a>
                </li>

                @for ($i = 1; $i <= $payments->lastPage(); $i++)
                    <li class="page-item {{ $payments->currentPage() == $i ? 'active' : '' }}">
                        <a class="page-link" href="{{ $payments->url($i) }}">{{ $i }}</a>
                    </li>
                @endfor

                <li class="page-item {{ !$payments->hasMorePages() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $payments->nextPageUrl() ?? '#' }}">&raquo;</a>
                </li>
            </ul>
        @endif

        @endif
    </div>

    <x-admin-footer></x-admin-footer>
</body>
</html>
