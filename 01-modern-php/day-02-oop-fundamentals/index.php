<?php

declare(strict_types=1);

use App\BankTransferGateway;
use App\CardPaymentGateway;
use App\InMemoryOrderRepository;
use App\Order;
use App\OrderItem;
use App\OrderService;

require_once __DIR__ . '/src/OrderItem.php';
require_once __DIR__ . '/src/PaymentStatus.php';
require_once __DIR__ . '/src/Order.php';
require_once __DIR__ . '/src/PaymentGateway.php';
require_once __DIR__ . '/src/CardPaymentGateway.php';
require_once __DIR__ . '/src/BankTransferGateway.php';
require_once __DIR__ . '/src/OrderRepository.php';
require_once __DIR__ . '/src/InMemoryOrderRepository.php';
require_once __DIR__ . '/src/OrderService.php';

$item1 = new OrderItem('Shirt', 2000, 2);
$item2 = new OrderItem('Shoes', 6000, 1);

$order = new Order(
    customerId: 123,
    items: [$item1, $item2],
);

// Choose the implementation here. OrderService itself does not change.
$gateway = new CardPaymentGateway();
// $gateway = new BankTransferGateway();

$repository = new InMemoryOrderRepository();

$orderService = new OrderService(
    gateway: $gateway,
    orderRepository: $repository,
);

$paymentSucceeded = $orderService->process($order);

echo 'Order total: $' . number_format($order->calculateTotal() / 100, 2) . PHP_EOL;
echo 'Payment result: ' . ($paymentSucceeded ? 'success' : 'failure') . PHP_EOL;
echo 'Final payment status: ' . $order->getPaymentStatus()->value . PHP_EOL;
