<?php

namespace App\View\Components;

use App\Models\Vendor;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class CardVendor extends Component
{
    public Vendor $vendor;
    public bool $isFavorited;
    public array $deliverySlots;

    public function __construct(Vendor $vendor)
    {
        $this->vendor = $vendor;

        $this->isFavorited = $vendor->favoriteVendors->contains(Auth::id());

        // Determine available delivery slots
        $this->deliverySlots = [];

        if ($vendor->breakfast_delivery ?? false) {
            $this->deliverySlots[] = 'breakfast';
        }
        if ($vendor->lunch_delivery ?? false) {
            $this->deliverySlots[] = 'lunch';
        }
        if ($vendor->dinner_delivery ?? false) {
            $this->deliverySlots[] = 'dinner';
        }
    }

    public function render()
    {
        return view('components.card-vendor');
    }
}
