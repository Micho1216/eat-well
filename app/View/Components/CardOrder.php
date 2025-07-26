<?php

namespace App\View\Components;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CardOrder extends Component
{
    /**
     * Create a new component instance.
     */

     public $order;
     public $status;
    public function __construct($order)
    {
        $this->order = $order;
        
        if($order->isCancelled == 1) {
            $this->status = 'cancelled';
        } else if (Carbon::now()->greaterThan($order->endDate)){
            $this->status = 'finished';
        } else if (Carbon::now()->lessThan($order->startDate)){
            $this->status = 'upcoming';
        } else {
            $this->status = 'active';
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.card-order');
    }
}
