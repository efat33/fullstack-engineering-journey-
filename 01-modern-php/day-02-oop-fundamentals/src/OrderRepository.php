<?php

declare(strict_types=1);

namespace App;

interface OrderRepository
{
    public function save(Order $order): void;
}
