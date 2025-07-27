@extends('components.vendor-nav')

@section('title', 'EatWell | Vendor Orders')

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/manageOrder.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous">
    </script>
@endsection

@section('content')
    <main class="container py-3">

        {{-- ---------- HEADER (search & filter) ---------- --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4">
            <h1 class="mb-3 mb-md-0">{{ __('manage-order.manage_orders') }}</h1>
            <div class="d-flex gap-2">
                <input id="search-input" type="text" class="form-control form-control-sm"
                    placeholder="{{ __('manage-order.search_by_order') }}" />
                <select id="package-filter" class="form-select form-select-sm">
                    <option value="all">{{ __('manage-order.all_packages') }}</option>
                    @foreach ($packages as $pkg)
                        <option value="{{ $pkg }}">{{ $pkg }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- ---------- TAB sebagai LINK ---------- --}}
        <div class="mb-4">
            <a href="{{ route('orders.index', ['week' => 'current']) }}"
                class="btn tab-btn {{ request('week', 'current') === 'current' ? 'active' : '' }} me-2">
                {{ __('manage-order.this_week') }}
            </a>

            <a href="{{ route('orders.index', ['week' => 'next']) }}"
                class="btn tab-btn {{ request('week') === 'next' ? 'active' : '' }}">
                {{ __('manage-order.next_week') }}
            </a>
        </div>

        <p id="empty-msg" class="text-center fw-bold py-5" style="display:none;">{{ __('manage-order.no_orders_yet') }}</p>

        <div class="row g-4" id="order-container"></div>
    </main>
@endsection

@section('scripts')
    {{-- ---------- SCRIPT ---------- --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        window.orderData = {
        orders: @json($orders),
        isNextWeek: {{ request('week') === 'next' ? 'true' : 'false' }},
        trans: {
            are_you_sure: "{{ __('manage-order.are_you_sure') }}",
            cancel_message: "{{ __('manage-order.cancel_message') }}",
            yes_cancel: "{{ __('manage-order.yes_cancel') }}",
            no: "{{ __('manage-order.no') }}",
            prepared: "{{ __('manage-order.prepared') }}",
            delivered: "{{ __('manage-order.delivered') }}",
            arrived: "{{ __('manage-order.arrived') }}",
            decline_order: "{{ __('manage-order.decline_order') }}",
            invalid_delivery_date: "{{ __('manage-order.invalid_delivery_date') }}",
            breakfast: "{{ __('manage-order.breakfast') }}",
            lunch: "{{ __('manage-order.lunch') }}",
            dinner: "{{ __('manage-order.dinner') }}"
        }
    };
    </script>
    <script src="{{ asset('js/vendor/manageOrder.js') }}"></script>

    {{-- SweetAlert & Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> {{-- <<< NEW --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection
