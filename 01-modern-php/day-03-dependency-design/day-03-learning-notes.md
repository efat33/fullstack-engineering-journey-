# Day 3 — Dependency Design

## Learning goals

The goal of Day 3 was to understand how to design PHP classes so that their dependencies are explicit, replaceable, and testable.

By the end of this day, the key ideas were:

- Depend on abstractions at important boundaries rather than hard-coding infrastructure details.
- Inject dependencies instead of constructing them inside business services.
- Choose constructor injection for essential dependencies and method injection for operation-specific dependencies.
- Wire the application manually at its composition root.
- Use fake implementations to test business logic without external services.
- Understand what a Dependency Injection (DI) container automates.

---

## 1. Dependency Inversion Principle (DIP)

DIP is one of the SOLID principles.

Its central idea:

> High-level modules should not depend directly on low-level modules. Both should depend on abstractions.

For example, an order service should not be tightly coupled to `StripePaymentGateway`. It should depend on a `PaymentGateway` interface.

```php
interface PaymentGateway
{
    public function charge(float $amount): bool;
}

final class OrderService
{
    public function __construct(
        private PaymentGateway $paymentGateway
    ) {}
}
```

A concrete gateway implements the contract:

```php
final class StripePaymentGateway implements PaymentGateway
{
    public function charge(float $amount): bool
    {
        // Communicate with Stripe.
        return true;
    }
}
```

The service can work with another implementation without changing its business logic, as long as that implementation honors the contract.

### DIP vs. Dependency Injection

These terms are related, but they are not the same:

- **DIP** is an architectural principle that guides how dependencies should point.
- **Dependency Injection (DI)** is a technique for supplying a class with the dependencies it needs from the outside.

DI can help us implement DIP, but using DI alone does not automatically guarantee good architecture. For example, injecting a concrete infrastructure class can still leave a service tightly coupled to that implementation.

---

## 2. Loose coupling and dependency boundaries

Loose coupling does not mean that classes have no dependencies. It means that dependencies are controlled and that business logic is not unnecessarily tied to changeable implementation details.

Consider a registration service that directly creates its own database and email objects:

```php
final class UserRegistrationService
{
    public function register(): void
    {
        $repository = new MySQLUserRepository();
        $mailer = new GmailNotificationService();

        // Registration workflow...
    }
}
```

This makes the service responsible for both its business workflow and the construction of infrastructure objects.

A more flexible design introduces contracts:

```php
final class UserRegistrationService
{
    public function __construct(
        private UserRepository $repository,
        private NotificationService $notification
    ) {}
}
```

The application can then supply the implementations it needs.

### Useful dependency boundaries

A typical application separates concerns into areas such as:

- **Business logic:** rules and workflows that describe what the application does.
- **Infrastructure:** database, email, file storage, external APIs, and other technical details.
- **Application composition:** the place where concrete objects are created and connected.

Not every class needs an interface. Interfaces are especially useful at boundaries where implementations may vary, external systems are involved, or replacing a dependency is valuable for testing.

---

## 3. Constructor injection vs. method injection

The decision depends on when and how a dependency is needed.

### Constructor injection

Use constructor injection when a dependency is essential to the object's normal responsibility and should be available throughout its lifetime.

```php
final class UserRegistrationService
{
    public function __construct(
        private UserRepository $repository,
        private NotificationService $notification,
        private Logger $logger
    ) {}
}
```

The repository, notification service, and logger are core collaborators of this registration service.

### Method injection

Use method injection when a dependency is needed only for a particular operation, rather than for the object's general responsibilities.

```php
public function exportUsers(ReportExporter $exporter): string
{
    $users = $this->repository->all();

    return $exporter->export($users);
}
```

The exporter is only needed when someone requests an export. It does not need to be a permanent dependency of the service.

### Setter injection

Setter injection can be useful for genuinely optional or replaceable dependencies. Avoid it for essential dependencies because it can leave an object in an incomplete state after construction.

**Rule of thumb:** essential for the object's responsibility → constructor injection; needed only for one operation → consider method injection.

---

## 4. Manual dependency wiring and the composition root

Dependency Injection does not require a DI container. We can create the objects ourselves and pass them into the classes that need them.

For example:

```php
$httpClient = new CurlHttpClient();

$gateway = new StripePaymentGateway($httpClient);

$repository = new MySQLOrderRepository();
$logger = new FileLogger();

$orderService = new OrderService(
    $gateway,
    $repository,
    $logger
);
```

Notice the order:

1. Create the lower-level dependency (`HttpClient`).
2. Inject it into the gateway.
3. Create the other infrastructure dependencies.
4. Inject those dependencies into the application service.

The part of the application responsible for creating and connecting objects is often called the **composition root**.

Manual wiring is explicit and easy to understand in a small application. As the application grows and dependency graphs become larger, maintaining all the construction code can become cumbersome.

---

## 5. Testing with fake implementations

A service that depends on interfaces can be tested with fake implementations instead of real infrastructure.

For example, a fake repository can keep users in memory rather than connecting to a database:

```php
final class FakeUserRepository implements UserRepository
{
    public array $savedUsers = [];

    public function save(User $user): void
    {
        $this->savedUsers[] = $user;
    }

    public function all(): array
    {
        return $this->savedUsers;
    }
}
```

A test can inject this fake into the real service:

```php
$repository = new FakeUserRepository();
$notification = new FakeNotificationService();
$logger = new FakeLogger();

$service = new UserRegistrationService(
    $repository,
    $notification,
    $logger
);
```

This lets us test business behavior without requiring a real database or sending real notifications.

### Test doubles: useful distinctions

- **Test double:** the general term for an object used in place of a real dependency during a test.
- **Fake:** a working, simplified implementation, such as an in-memory repository.
- **Stub:** provides predefined responses to calls.
- **Spy:** records calls so the test can inspect how it was used.
- **Mock:** a test double configured with expectations about interactions.

These categories can overlap in some testing tools and discussions, but the distinctions are useful.

### Important testing boundary

A fake repository can test how the service behaves when saving succeeds or fails. It does not prove that the real database implementation works correctly. Real infrastructure should also be covered by appropriate integration tests.

---

## 6. Practical application: User registration and reporting

The Day 3 project uses a small registration application to bring the ideas together.

### Project structure

```text
day-03-dependency-design/
├── src/
│   ├── Contracts/
│   │   ├── UserRepository.php
│   │   ├── NotificationService.php
│   │   ├── Logger.php
│   │   └── ReportExporter.php
│   ├── Models/
│   │   └── User.php
│   ├── Services/
│   │   └── UserRegistrationService.php
│   └── Infrastructure/
│       ├── InMemoryUserRepository.php
│       ├── ConsoleNotificationService.php
│       ├── ArrayLogger.php
│       └── CsvReportExporter.php
├── tests/
│   └── run.php
├── bootstrap.php
├── index.php
└── learning-notes.md
```

### Responsibilities

- `User`: represents a user and protects its basic data invariants.
- `UserRepository`: contract for saving and retrieving users.
- `NotificationService`: contract for sending a welcome notification.
- `Logger`: contract for recording application messages.
- `ReportExporter`: contract for exporting a collection of users.
- `UserRegistrationService`: coordinates the registration workflow.
- Infrastructure classes: provide concrete implementations of the contracts.
- `bootstrap.php`: creates the concrete dependencies and injects them into the service.
- `tests/run.php`: tests the service using fake dependencies.

### Why one export method?

The service exposes one operation:

```php
public function exportUsers(ReportExporter $exporter): string
```

The caller can supply a CSV exporter, PDF exporter, or another implementation of `ReportExporter`.

The service does not need separate business methods such as `exportUsersAsCsv()` and `exportUsersAsPdf()` if the workflow is the same and the exporter contract adequately represents the operation.

This is an example of programming to an abstraction and keeping the service open to new implementations without changing its existing workflow.

### Production consideration: notification failure

The exercise catches a repository failure and logs it. The notification call is deliberately outside that `try` block.

In a production application, we would need to decide what should happen if notification delivery fails after the user has already been saved. Possible approaches include queuing the notification, retrying it, logging the failure, or reporting a partial outcome. The correct choice depends on the business requirements.

---

## 7. Dependency Injection Containers

A DI container is an object that helps create and resolve dependencies automatically.

Without a container, we manually construct the dependency graph:

```php
$repository = new MySQLUserRepository();
$notification = new EmailNotificationService();
$logger = new FileLogger();

$service = new UserRegistrationService(
    $repository,
    $notification,
    $logger
);
```

With a container, we configure which implementations should be used and ask the container to resolve the service.

A conceptual example:

```php
$container->bind(
    UserRepository::class,
    MySQLUserRepository::class
);

$service = $container->make(UserRegistrationService::class);
```

The container uses the binding to decide which implementation satisfies the `UserRepository` dependency. It can inspect the constructor and resolve the dependencies needed to build the service.

### DI vs. DI container

- **Dependency Injection:** the technique of supplying dependencies from outside a class.
- **DI container:** a tool that automates object creation and dependency resolution.

A container does not remove architectural decisions. We still decide which implementation should be used and configure the bindings. The container automates much of the object construction and wiring.

### The custom Container class: what to understand now

The small reflection-based `Container` example demonstrates the mechanism:

1. `bind()` stores a mapping from an abstraction to a concrete class.
2. `make()` looks up the concrete class to construct.
3. Reflection inspects the class constructor.
4. The container reads constructor parameter types.
5. It recursively calls `make()` to resolve those dependencies.
6. It creates the object and returns it.

For now, understanding this overall flow is enough. You do **not** need to memorize or independently write the reflection code yet.

The implementation is intentionally simplified. A production container needs to consider many additional cases, such as scalar constructor values, shared instances, factory callbacks, circular dependencies, and more complex type declarations.

We will revisit containers when we work with Laravel's service container. At that point, we can study the practical behavior and then return to the underlying mechanics with more context.

### `bind()` vs. `singleton()`

In Laravel-style containers:

- `bind()` generally creates a new instance each time the binding is resolved.
- `singleton()` reuses the same instance for the lifetime of the container.

A singleton is not automatically better. Sharing mutable state can create problems, particularly in long-running application processes.

---

## Review: key distinctions

| Concept | Meaning |
|---|---|
| DIP | Architectural principle about the direction of dependencies |
| DI | Technique for supplying dependencies from outside a class |
| DI container | Tool that automates dependency construction and resolution |
| Interface | Contract describing expected behavior |
| Concrete implementation | Class that fulfills a contract |
| Composition | Building behavior by combining collaborating objects |
| Composition root | Place where application objects are created and connected |
| Fake | Simplified working implementation used in tests |

## Day 3 takeaways

1. A class should not be responsible for constructing all the infrastructure it depends on.
2. Depending on an abstraction makes implementations easier to replace.
3. Constructor injection is appropriate for essential dependencies; method injection is useful for operation-specific dependencies.
4. Manual wiring is a valid form of DI and makes the dependency graph explicit.
5. Fakes let us test application behavior without depending on real external systems.
6. A DI container automates object creation and wiring; it does not replace good architecture or application configuration.
7. We only need a high-level understanding of the custom reflection-based container for now. We will deepen this when we work with Laravel.

## Running the project

From the Day 3 project directory:

```bash
php index.php
php tests/run.php
```

The first command runs the sample application. The second runs the project's lightweight test runner.

---

**Day 3 complete — Dependency Design**
