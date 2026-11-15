<?php

declare(strict_types=1);

namespace App;

use InvalidArgumentException;

final class Order
{
    /** @var list<OrderItem> */
    private array $items;

    private PaymentStatus $paymentStatus = PaymentStatus::Pending;

    /**
     * @param list<OrderItem> $items
     */
    public function __construct(
        private int $customerId,
        array $items,
    ) {
        if ($this->customerId <= 0) {
            throw new InvalidArgumentException('Customer ID must be greater than zero.');
        }

        if ($items === []) {
            throw new InvalidArgumentException('An order must contain at least one item.');
        }

        foreach ($items as $item) {
            if (!$item instanceof OrderItem) {
                throw new InvalidArgumentException(
                    'Every order item must be an OrderItem instance.'
                );
            }
        }

        $this->items = array_values($items);
    }

    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    /** @return list<OrderItem> */
    public function getItems(): array
    {
        // Returning the array doesn't let callers replace elements in this
        // Order's private array. The contained objects are still objects.
        return $this->items;
    }

    public function calculateTotal(): int
    {
        $total = 0;

        foreach ($this->items as $item) {
            $total += $item->getSubtotal();
        }

        return $total;
    }

    public function getPaymentStatus(): PaymentStatus
    {
        return $this->paymentStatus;
    }

    public function markAsPaid(): void
    {
        $this->paymentStatus = PaymentStatus::Paid;
    }

    public function markPaymentAsFailed(): void
    {
        $this->paymentStatus = PaymentStatus::Failed;
    }
}
