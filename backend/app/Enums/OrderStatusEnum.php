<?php

namespace App\Enums;

enum OrderStatusEnum: string
{
    case PENDING   = 'pending';
    case DONE      = 'done';
    case CANCELLED = 'cancelled';
}
