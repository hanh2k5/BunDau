<?php

namespace App\Enums;

enum PaymentMethodEnum: string
{
    case CASH = 'cash';
    case TRANSFER = 'transfer';

    public function label(): string
    {
        return match($this) {
            self::CASH => 'Tiền mặt',
            self::TRANSFER => 'Chuyển khoản',
        };
    }
}
