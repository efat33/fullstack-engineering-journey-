<?php

declare(strict_types=1);

class Payment
{
    public function __construct(
        public readonly float $amount,
    ) {
        if ($this->amount <= 0) {
            throw new InvalidArgumentException(
                'Payment amount must be greater than zero.'
            );
        }
    }
}
