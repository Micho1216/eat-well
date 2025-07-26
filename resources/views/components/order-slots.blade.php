<div class="row order-details">
    @foreach ($filteredSlots as $slot)
        <div class="col-lg p-2 time-slot active">
            <div class="row mb-1 justify-content-between align-content-center"
                data-bs-toggle="collapse" data-bs-target="#{{ $slot['key'] }}-packages"
                role="button" aria-expanded="false"
                aria-controls="{{ $slot['key'] }}-packages">

                <div class="time-slot-type font-400 w-auto p-0 ps-1">
                    {{ $slot['label'] }}
                </div>

                <div class="delivery-status hug-content align-self-center me-1">
                    {{ $slot['statusText'] }}
                </div>
            </div>

            <div class="collapse" id="{{ $slot['key'] }}-packages">
                @foreach ($slot['packages'] as $item)
                    <div class="row p-0 package justify-content-between align-content-center">
                        <div class="w-75 mb-1 p-0 ps-1 package-name">{{ $item['package'] }}</div>
                        <div class="w-auto align-self-center me-1 quantity">x {{ $item['quantity'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
