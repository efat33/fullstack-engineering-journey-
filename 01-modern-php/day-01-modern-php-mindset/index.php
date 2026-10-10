<?php

declare(strict_types=1);

require_once __DIR__ . '/src/Payment.php';

$payment = new Payment(500.00);

echo $payment->amount;
