# Day 1 — Modern PHP Mindset

## Overview

This project demonstrates modern PHP programming practices by building a small payment module using PHP 8.4.

The goal is to move beyond loosely structured procedural PHP and write code with explicit types, clear responsibilities, and input validation.

## Project Structure

```text
day-01-modern-php-mindset/
├── README.md
├── index.php
└── src/
    ├── Payment.php
    ├── PaymentStatus.php
    └── DiscountCalculator.php
```

## Features

- **Strict typing:** Uses `declare(strict_types=1);` to reduce unintended scalar type coercion.
- **Type declarations:** Defines parameter types, property types, and return types.
- **Constructor property promotion:** Declares and initializes properties directly in the constructor.
- **Readonly properties:** Prevents reassignment of payment amount and reference after initialization.
- **Enums:** Represents payment status using a fixed set of valid cases.
- **Input validation:** Rejects negative payment amounts, empty references, negative prices, and discount percentages outside the range of 0–100.
- **Exceptions:** Uses `InvalidArgumentException` to report invalid arguments.
- **Static methods:** Calculates discounts without requiring a `DiscountCalculator` instance.

## Requirements

- PHP 8.1 or newer
- PHP CLI

Developed and tested with PHP 8.4.

## How to Run

Navigate to this directory in your terminal and run:

```bash
php index.php
```

The script demonstrates creating a payment, updating its status, calculating a discount, and displaying the final price.

## Example

For a payment of 1,000 with a 15% discount:

- Original price: 1,000
- Discount amount: 150
- Final price: 850

## Validation Behavior

The `Payment` constructor rejects amounts less than or equal to zero and references containing only whitespace.

The discount calculator rejects negative prices and discount percentages below 0 or above 100.

Invalid arguments throw `InvalidArgumentException`. The calling code is responsible for catching and handling these exceptions where appropriate.

## Key Learning Points

1. Type declarations help define what values a function or property accepts.
2. Type declarations alone do not enforce business rules.
3. Nullable types, union types, and enums help express allowed values more clearly.
4. Exceptions separate failure reporting from failure handling.
5. Constructor validation helps prevent invalid objects from being created.
6. `readonly` protects properties from reassignment, while mutable properties can change when needed.

## Production Consideration

This project is a learning exercise, not a production payment system. Real financial applications should use integer minor units (such as cents or paisa) or a suitable decimal-money approach rather than relying on floating-point arithmetic for monetary calculations. Payment processing also requires appropriate authorization, transaction handling, and other safeguards.

## Learning Roadmap

**Month 1: Modern PHP + WordPress**

- Day 1: Modern PHP mindset and type-safe code
