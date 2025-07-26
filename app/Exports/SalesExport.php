<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromView;

class SalesExport implements FromView
{
    public $orders;
    public $totalSales;
    public $startDate;
    public $endDate;

    public function __construct($orders, $totalSales, $startDate, $endDate)
    {
        $this->orders = $orders;
        $this->totalSales = $totalSales;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }
    public function view(): View
    {
        return view('catering.salesTable', ['orders' => $this->orders, 'totalSales' => $this->totalSales, 'startDate' => $this->startDate, 'endDate' => $this->endDate]);
    }
}
