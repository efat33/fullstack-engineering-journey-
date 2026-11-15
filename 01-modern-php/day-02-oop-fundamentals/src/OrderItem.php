<?php

declare(strict_types=1);

namespace App;

use InvalidArgumentException;

final class OrderItem
{
    public function __construct(
        private string $productName,
        private int $unitPriceInCents,
        private int $quantity,
    ) {
        if (trim($this->productName) === '') {
            throw new InvalidArgumentException('Product name cannot be empty.');
        }

        if ($this->unitPriceInCents < 0) {
            throw new InvalidArgumentException('Unit price cannot be negative.');
        }

        if ($this->quantity <= 0) {
            throw new InvalidArgumentException('Quantity must be greater than zero.');
        }
    }

    public function getProductName(): string
    {
        return $this->productName;
    }

    public function getUnitPriceInCents(): int
    {
        return $this->unitPriceInCents;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getSubtotal(): int
    {
        return $this->unitPriceInCents * $this->quantity;
    }
}
