<?php

declare(strict_types=1);

namespace App;

interface PaymentGateway
{
    /**
     * Attempts to charge an amount expressed in cents.
     *
     * Returns true when the gateway reports success, otherwise false.
     */
    public function charge(int $amountInCents): bool;
}
