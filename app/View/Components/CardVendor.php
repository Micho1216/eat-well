<?php

namespace App\View\Components;

use App\Models\Address;
use App\Models\Province;
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
    public bool $tooFar;

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

        $user_adr_id = session('address_id');
        $user_province = Address::find($user_adr_id)?->provinsi;
        
        $this->tooFar = $vendor->provinsi === $user_province ? 0 : 1;
    }

    public function render()
    {
        return view('components.card-vendor');
    }
}
