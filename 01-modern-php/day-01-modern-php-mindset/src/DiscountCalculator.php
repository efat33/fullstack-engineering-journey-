<?php

declare(strict_types=1);

final class DiscountCalculator
{
    public static function calculate(
        float $price,
        float $discountPercentage,
    ): float {
        if ($price < 0) {
            throw new InvalidArgumentException(
                'Price cannot be negative.'
            );
        }

        if ($discountPercentage < 0 || $discountPercentage > 100) {
            throw new InvalidArgumentException(
                'Discount percentage must be between 0 and 100.'
            );
        }

        return $price * ($discountPercentage / 100);
    }
}
