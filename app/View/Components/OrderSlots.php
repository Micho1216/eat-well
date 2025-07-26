<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class OrderSlots extends Component
{
    public array $slotMap;
    public array $slotOrder;
    public array $slotLabelMap;
    public array $filteredSlots;

    public function __construct(array $slotMap)
    {
        $this->slotMap = $slotMap;

        $this->slotOrder = ['morning', 'afternoon', 'evening'];

        $this->slotLabelMap = [];
        foreach ($this->slotOrder as $slot) {
            $this->slotLabelMap[$slot] = __('slot.' . $slot);
        }

        $this->filteredSlots = $this->filterSlots();
    }

    protected function filterSlots(): array
    {
        $result = [];
        $canShow = true;

        foreach ($this->slotOrder as $slotKey) {
            if (!isset($this->slotMap[$slotKey])) {
                continue;
            }

            if ($canShow) {
                $slotData = $this->slotMap[$slotKey];

                $status = $slotData['deliveryStatus'];
                $statusValue = strtolower($status->status->value ?? $status->status ?? '');
                $statusText = $statusValue
                    ? __('delivery_status.' . $statusValue)
                    : '-';

                $result[] = [
                    'key' => $slotKey,
                    'label' => $this->slotLabelMap[$slotKey] ?? ucfirst($slotKey),
                    'statusText' => $statusText,
                    'statusValue' => $statusValue,
                    'packages' => $slotData['packages'],
                ];

                $canShow = $statusValue === 'arrived';
            }
        }

        return $result;
    }


    public function render()
    {
        return view('components.order-slots');
    }
}
