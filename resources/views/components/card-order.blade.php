@php
    use Carbon\Carbon;
@endphp

<div class="card-order" data-order-id="{{ $order->orderId }}">
    <div class="card-header">
        <div class="left-container">
            <div class="text-wrapper vendor-name-wrapper">
                <h5 class="">{{ $order->vendor->name }}</h5>
            </div>
            <a href="{{ route('catering-detail', $order->vendor) }}" class="text-wrapper btn-view">
                <p>{{__('customer/order.card_view')}}</p>
            </a>
        </div>
        <div class="right-container">
            <div class="text-wrapper order-date">
                <p class="date">{{ Carbon::parse($order->startDate)->format('d/m/Y') }}</p>
                <p class="date">-</p>
                <p class="date">{{ Carbon::parse($order->endDate)->format('d/m/Y') }}</p>
            </div>
            <div class="text-wrapper label-status status-{{ $status }}">
                {{ ucfirst(__('customer/order.' . $status)) }}
            </div>
        </div>
    </div>


    <a href="{{ route('order-detail', $order) }}" class="card-content-wrapper text-decoration-none">
        @foreach ($order->orderItems as $item)
            <div class="card-content">
                <div class="image-wrapper">
                    {{-- <img src="{{$item->package->imgPath ? asset($item->package->imgPath) : asset('asset/catering-detail/logo-packages.png')}}" alt="Gambar Paket"> --}}
                    <img src="{{ $item->package->imgPath ? asset('asset/menus/' . $item->package->imgPath) : asset('asset/menus/logo-packages.png') }}"
                        alt="gambar paket">
                </div>
                <div class="right-container">
                    <div class="package-detail">
                        <div class="text-container detail-primary">{{ $item->package->name }}</div>
                        <div class="text-container d-flex flex-row flex-md-column column-gap-2">
                            <div class="text-wrapper detail-secondary">
                                {{__('customer/order.card_variant')}}: {{ __('customer/order.' . $item->packageTimeSlot) }}
                            </div>
                            <div class="text-wrapper detail-secondary">
                                x{{ $item->quantity }}
                            </div>
                        </div>
                    </div>
                    <div class="price-wrapper">
                        <div class="text-wrapper">
                            <p>
                                Rp {{ number_format($item->price, 2, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </a>
    <div class="card-bottom">
        <div class="left-container">
            @if ($status == 'upcoming')
                <div class="d-flex flex-row">
                    <button class="btn btn-danger open-cancel-modal" id="open-cancel-modal" data-order-id="{{ $order->orderId }}">
                        {{__('customer/order.cancel')}}
                    </button>
                </div>
            @elseif ($status == 'finished')
                <div class="rating-container">
                    @if ($order->vendorReview)
                        <span class="detail-primary">{{__('customer/order.rated')}}: </span>
                    @else
                        <span class="detail-primary">{{__('customer/order.rate')}} </span>
                    @endif
                    <div class="rating-icon-list">
                        @if ($order->vendorReview)
                            @for ($i = 1; $i <= 5; $i++)
                                <span
                                    class="material-symbols-outlined star-icon{{ $i <= $order->vendorReview->rating ? ' choosen' : '' }}"
                                    style="cursor:default;">star</span>
                            @endfor
                        @else
                            @for ($i = 1; $i <= 5; $i++)
                                <button type="button" class="material-symbols-outlined star-icon-btn"
                                    data-index="{{ $i }}">star</button>
                            @endfor
                        @endif
                    </div>
                </div>
            @endif
        </div>
        <div class="right-container">
            <div class="total-container">
                <span class="detail-primary">Total {{ count($order->orderItems) }} {{__('customer/order.packages')}}: </span>
                <span class="detail-highlight">Rp
                    {{ number_format($order->totalPrice, 2, ',', '.') }}</span>
            </div>
        </div>
    </div>
</div>
