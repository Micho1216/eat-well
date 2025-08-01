<?php

namespace App\Enums;

enum DeliveryStatuses: string
{
    case Prepared = 'Prepared';
    case Delivered = 'Delivered';
    case Arrived = 'Arrived';
    case Cancelled = 'Cancelled';
}