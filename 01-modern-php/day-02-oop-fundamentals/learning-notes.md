# Day 2 — OOP Fundamentals in PHP

## Project goal

This project models a small order-processing workflow. It is designed to practise:

- Classes and objects
- Constructors and constructor property promotion
- Visibility and encapsulation
- Validation and business rules
- Composition
- Interfaces and implementation classes
- Dependency injection
- Separating domain objects, workflow coordination, and persistence
- Open/Closed Principle
- Enums and typed properties

This is a learning demo, not production payment software. The gateways simulate successful/failed outcomes; they do not contact a real payment provider. The repository stores snapshots in memory, not in a database.

---

## 1. Classes and objects

A **class** defines the data and behavior available to a kind of object. An **object** is an instance of a class.

```php
$itemA = new OrderItem('Shirt', 2000, 2);
$itemB = new OrderItem('Shoes', 6000, 1);
```

These are two separate objects with their own property values. Updating one instance's state does not ordinarily update another instance's state.

### Class vs Object

- The class defines the structure and behavior of its objects.
- Each object has its own instance properties.
- Changing an instance property normally affects that object, not other objects created from the same class.

## 2. Constructors and valid objects

`__construct()` runs when a new object is created. It can initialize properties and reject invalid input.

Requiring a value and validating it are different things. For example, `int $customerId` requires an integer, but an integer such as `0` may still violate the application's business rules.

This project validates:

- A product name must not be blank.
- A unit price must not be negative.
- A quantity must be greater than zero.
- A customer ID must be greater than zero.
- An order must contain at least one item.
- Every item in the order must be an `OrderItem`.

When input violates these rules, the constructor throws `InvalidArgumentException`.

### — Constructors: why do we need them?

A constructor is useful for two main reasons:

1. Initialize the object with the data it needs.
2. Establish its initial validity so the object doesn't start in an invalid state.

## 3. Visibility and encapsulation

- `public`: accessible from outside the class.
- `private`: accessible only within the class itself.
- `protected`: accessible within the class and its subclasses.

Encapsulation means keeping control over internal state and providing deliberate public methods for operations. For example, `Order::$paymentStatus` is private. Other code reads it through `getPaymentStatus()` and changes it through `markAsPaid()` or `markPaymentAsFailed()`.

A public property would let any caller change it without going through the class's intended operations.

## 4. PHP types versus business rules

This declaration:

```php
public function __construct(private array $items)
```

only says `$items` must be an array. It does not require the array to be non-empty or require every element to be an `OrderItem`.

That's why `Order` checks the array itself:

```php
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
```

The `@param list<OrderItem> $items` PHPDoc annotation helps IDEs and static-analysis tools understand the intended element type. It does not replace runtime validation.

**Takeaway:** a value can satisfy a PHP type declaration and still be invalid business data.

## 5. Why `OrderItem` is a separate class

An order can contain multiple items. Each item has its own product name, unit price, quantity, and subtotal.

`OrderItem` owns the subtotal calculation:

```php
public function getSubtotal(): int
{
    return $this->unitPriceInCents * $this->quantity;
}
```

`Order` owns the collection and calculates the overall total by asking each item for its subtotal. This avoids putting every item-specific detail and behavior inside the `Order` class.

## 6. Why `Order` calculates the order total

The total is a value derived from the order's own items, so the calculation naturally belongs to `Order`.

```php
public function calculateTotal(): int
{
    $total = 0;

    foreach ($this->items as $item) {
        $total += $item->getSubtotal();
    }

    return $total;
}
```

`OrderService` calls `$order->calculateTotal()` rather than implementing a second copy of the same calculation. This gives us one place to maintain the rule.

## 7. Why money is stored as integer cents

The project represents `$20.00` as `2000` cents, not as a floating-point value such as `20.00`. Binary floating-point cannot exactly represent every decimal fraction, which can create rounding surprises. Integer minor units are a common simple approach for this type of example.

A real system must also handle currency explicitly if it supports more than one currency.

## 8. Inheritance, abstract classes, and interfaces

### Inheritance

Inheritance uses `extends` and represents a parent/child relationship, often an “is-a” relationship. A child class inherits accessible behavior and can specialize it.

### Abstract class

An abstract class cannot be instantiated directly. It can provide shared state and concrete methods, as well as abstract methods that subclasses must implement.

### Interface

An interface defines a contract. A class that implements it must provide the required methods. PHP classes can implement multiple interfaces but can extend only one class.

In this project, `PaymentGateway` is an interface:

```php
interface PaymentGateway
{
    public function charge(int $amountInCents): bool;
}
```

`CardPaymentGateway` and `BankTransferGateway` implement that contract. We chose an interface because the service needs to know what a gateway can do, not how each provider implements it.

## 9. Composition

In this lesson, composition broadly describes objects working together by holding or using other objects.

`OrderService` uses a payment gateway and an order repository. It delegates those jobs rather than implementing payment-provider details or database logic itself.

Composition and interfaces are not alternatives:

- The **interface** defines the expected contract.
- **Composition** describes how an object collaborates with another object.

“Composition over inheritance” does not mean inheritance is never useful. It means not defaulting to inheritance just to reuse code when a collaborator would give a clearer, more flexible design.

## 10. Dependency injection

`OrderService` receives its dependencies through its constructor:

```php
public function __construct(
    private PaymentGateway $gateway,
    private OrderRepository $orderRepository,
) {
}
```

The type `PaymentGateway` does not cause PHP to instantiate a gateway automatically. Other code must create the concrete object and pass it in:

```php
$orderService = new OrderService(
    gateway: new CardPaymentGateway(),
    orderRepository: new InMemoryOrderRepository(),
);
```

To use bank transfer instead:

```php
$orderService = new OrderService(
    gateway: new BankTransferGateway(),
    orderRepository: new InMemoryOrderRepository(),
);
```

`OrderService` does not need to change. The code that assembles the objects chooses which implementation to inject. This also makes testing easier because a test can inject a fake gateway.

## 11. Open/Closed Principle

The Open/Closed Principle says a component should be open for extension but closed for modification.

For example, a new `PayPalGateway` could implement `PaymentGateway`. We should not have to edit `OrderService` just to support another gateway.

However, the code that chooses a gateway—perhaps a factory or configuration—may still need to change. The principle does not mean no file ever changes; it means stable business logic should not need constant edits to support new variants.

## 12. Order versus OrderService

### `Order`

Represents the order and owns its state and order-specific rules:

- customer ID
- items
- total calculation
- payment status

### `OrderService`

Coordinates the use case:

1. Save the order while its status is `Pending`.
2. Ask the payment gateway to charge the order total.
3. Mark the order `Paid` or `Failed`.
4. Save the updated status.

The service coordinates the workflow, but the order owns its lasting payment status.

## 13. Repository abstraction

`OrderRepository` defines a contract for saving an order. `InMemoryOrderRepository` implements it by storing simple snapshots in an array and printing a message.

It is deliberately not a database repository. Later, a database-backed implementation could implement `OrderRepository`, allowing the service to continue depending on the abstraction rather than a specific storage technology.

## 14. Project structure

```text
day-02-oop-fundamentals/
├── index.php
├── learning-notes.md
└── src/
    ├── OrderItem.php
    ├── Order.php
    ├── PaymentStatus.php
    ├── PaymentGateway.php
    ├── CardPaymentGateway.php
    ├── BankTransferGateway.php
    ├── OrderRepository.php
    ├── InMemoryOrderRepository.php
    └── OrderService.php
```

| File                          | Responsibility                                               |
| ----------------------------- | ------------------------------------------------------------ |
| `OrderItem.php`               | Validates one item and calculates its subtotal               |
| `Order.php`                   | Validates the order, owns its items/status, calculates total |
| `PaymentStatus.php`           | Defines the allowed status values                            |
| `PaymentGateway.php`          | Defines the payment contract                                 |
| `CardPaymentGateway.php`      | Demo card gateway implementation                             |
| `BankTransferGateway.php`     | Demo bank transfer implementation                            |
| `OrderRepository.php`         | Defines the persistence contract                             |
| `InMemoryOrderRepository.php` | Stores snapshots in memory for demonstration                 |
| `OrderService.php`            | Coordinates persistence and payment                          |
| `index.php`                   | Creates and connects the objects, then runs the example      |

## 15. How to run the project

From this folder, run:

```bash
php index.php
```

Expected output:

```text
Saved order for customer 123 with status: pending
Attempting card payment of $100.00
Saved order for customer 123 with status: paid
Order total: $100.00
Payment result: success
Final payment status: paid
```

To try the other gateway, change this line in `index.php`:

```php
$gateway = new CardPaymentGateway();
```

to:

```php
$gateway = new BankTransferGateway();
```

No change to `OrderService` is needed.

## 16. Important production caveat

The code is for learning, not a production-ready payment implementation. Real systems need to account for provider timeouts and uncertain payment outcomes, retries, idempotency, logging, secure provider integration, and database consistency. A boolean return value alone cannot model every real payment outcome.

## 17. Review checklist

After running the project, make sure you can explain:

- Why `OrderItem` and `Order` are separate classes.
- Why `Order` calculates the total instead of `OrderService`.
- Why constructors validate business rules.
- Why `PaymentGateway` is an interface.
- Why `OrderService` receives dependencies instead of creating them internally.
- Why switching from card to bank transfer doesn't require changing `OrderService`.
- Why `OrderRepository` is separate from the service.
- Which parts of this demo would need improvement before production use.
