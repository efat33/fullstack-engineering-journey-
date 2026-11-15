<?php

declare(strict_types=1);

namespace App;

final class InMemoryOrderRepository implements OrderRepository
{
    /** @var list<array{customerId: int, totalInCents: int, status: string}> */
    private array $savedOrders = [];

    public function save(Order $order): void
    {
        // Store a simple snapshot so each save records the status at that time.
        $snapshot = [
            'customerId' => $order->getCustomerId(),
            'totalInCents' => $order->calculateTotal(),
            'status' => $order->getPaymentStatus()->value,
        ];

        $this->savedOrders[] = $snapshot;

        echo "Saved order for customer {$snapshot['customerId']} "
            . "with status: {$snapshot['status']}"
            . PHP_EOL;
    }

    /** @return list<array{customerId: int, totalInCents: int, status: string}> */
    public function all(): array
    {
        return $this->savedOrders;
    }
}
