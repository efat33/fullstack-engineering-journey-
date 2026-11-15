<?php

declare(strict_types=1);

namespace App;

final class BankTransferGateway implements PaymentGateway
{
    public function charge(int $amountInCents): bool
    {
        if ($amountInCents <= 0) {
            return false;
        }

        // Demo only: a real implementation would initiate/verify a transfer.
        echo 'Attempting bank transfer of $'
            . number_format($amountInCents / 100, 2)
            . PHP_EOL;

        return true;
    }
}
