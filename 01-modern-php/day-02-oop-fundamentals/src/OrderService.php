<?php

declare(strict_types=1);

namespace App;

final class OrderService
{
    public function __construct(
        private PaymentGateway $gateway,
        private OrderRepository $orderRepository,
    ) {
    }

    public function process(Order $order): bool
    {
        // Record the order before attempting payment.
        $this->orderRepository->save($order);

        $paymentSucceeded = $this->gateway->charge($order->calculateTotal());

        if ($paymentSucceeded) {
            $order->markAsPaid();
        } else {
            $order->markPaymentAsFailed();
        }

        // Record the updated payment status.
        $this->orderRepository->save($order);

        return $paymentSucceeded;
    }
}
