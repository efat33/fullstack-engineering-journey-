<?php

declare(strict_types=1);

require_once __DIR__ . '/src/PaymentStatus.php';
require_once __DIR__ . '/src/Payment.php';
require_once __DIR__ . '/src/DiscountCalculator.php';

try {
    $payment = new Payment(
        amount: 1000.0,
        reference: 'PAY-001',
    );

    echo "Payment reference: {$payment->reference}" . PHP_EOL;
    echo "Payment amount: {$payment->amount}" . PHP_EOL;
    echo "Payment status: {$payment->status->value}" . PHP_EOL;

    $payment->status = PaymentStatus::Completed;

    echo "Updated status: {$payment->status->value}" . PHP_EOL;

    $discount = DiscountCalculator::calculate(1000.0, 15.0);

    echo "Discount amount: {$discount}" . PHP_EOL;
    echo "Final price: " . (1000.0 - $discount) . PHP_EOL;
} catch (InvalidArgumentException $exception) {
    echo "Invalid input: {$exception->getMessage()}" . PHP_EOL;
}
