<?php

declare(strict_types=1);

require_once __DIR__ . '/PaymentStatus.php';

class Payment
{
    public function __construct(
        public readonly float $amount,
        public readonly string $reference,
        public PaymentStatus $status = PaymentStatus::Pending,
    ) {
        if ($this->amount <= 0) {
            throw new InvalidArgumentException(
                'Payment amount must be greater than zero.'
            );
        }

        if (trim($this->reference) === '') {
            throw new InvalidArgumentException(
                'Payment reference cannot be empty.'
            );
        }
    }
}
