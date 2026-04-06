<?php

declare(strict_types=1);

/*
 * Comprehensive PHP 8.4+ and 8.5 feature showcase
 * Tests semantic parsing of modern PHP features
 */

namespace App\Service;

use App\Model\User;
use App\Repository\UserRepository;
use Psr\Log\LoggerInterface;

// ============================================================================
// PHP 8.5: Pipe Operator
// ============================================================================

$result = 'hello world'
    |> strtoupper(...)
    |> str_split(...)
    |> array_filter(..., fn($c) => $c !== ' ')
    |> implode('', ...);

$users = getUsers()
    |> array_filter(..., fn($u) => $u->active)
    |> array_map(..., fn($u) => $u->name)
    |> array_unique(...);

// Pipe with custom functions
function double(int $x): int
{
    return $x * 2;
}

$value = 5
    |> double(...)
    |> double(...)
    |> fn($x) => $x + 10;

// ============================================================================
// PHP 8.4: Property Hooks
// ============================================================================

class Person
{
    // Property hook: get
    public string $fullName {
        get => $this->firstName . ' ' . $this->lastName;
    }

    // Property hook: set with validation
    public int $age {
        set {
            if ($value < 0) {
                throw new \InvalidArgumentException('Age cannot be negative');
            }
            $this->age = $value;
        }
    }

    // Property hook: get and set
    public string $email {
        get => strtolower($this->email);
        set => $this->email = filter_var($value, FILTER_VALIDATE_EMAIL)
            ? $value
            : throw new \InvalidArgumentException('Invalid email');
    }

    public function __construct(
        private string $firstName,
        private string $lastName,
    ) {}
}

// ============================================================================
// PHP 8.4: Asymmetric Visibility
// ============================================================================

class Account
{
    public private(set) string $id;
    public protected(set) float $balance;
    public private(set) \DateTimeImmutable $createdAt;

    public function __construct(string $id)
    {
        $this->id = $id;
        $this->balance = 0.0;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function deposit(float $amount): void
    {
        $this->balance += $amount;
    }
}

// ============================================================================
// Attributes (PHP 8.0+)
// ============================================================================

#[Route('/api/users')]
#[Security('is_granted("ROLE_ADMIN")')]
class UserController
{
    #[Route('/{id}', methods: ['GET'])]
    #[Cache(ttl: 3600)]
    public function show(int $id): User
    {
        return $this->repository->find($id);
    }

    #[Route('', methods: ['POST'])]
    #[Validate(rules: ['email' => 'required|email'])]
    public function create(array $data): User
    {
        return $this->repository->create($data);
    }
}

// ============================================================================
// Enums (PHP 8.1+)
// ============================================================================

enum Status: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Pending = 'pending';

    public function label(): string
    {
        return match($this) {
            self::Active => 'Active User',
            self::Inactive => 'Inactive User',
            self::Pending => 'Pending Approval',
        };
    }
}

enum Permission
{
    case Read;
    case Write;
    case Delete;
}

// ============================================================================
// Match Expression (PHP 8.0+)
// ============================================================================

$status = Status::Active;
$color = match($status) {
    Status::Active => 'green',
    Status::Inactive => 'red',
    Status::Pending => 'yellow',
};

$result = match($value) {
    0 => 'zero',
    1, 2, 3 => 'low',
    4, 5, 6 => 'medium',
    default => 'high',
};

// ============================================================================
// Arrow Functions (PHP 7.4+)
// ============================================================================

$multiply = fn($a, $b) => $a * $b;
$square = fn($x) => $x ** 2;
$users = array_map(fn($u) => $u->name, $userList);
$active = array_filter($users, fn($u) => $u->isActive());

// ============================================================================
// Closures and Callable
// ============================================================================

$closure = function($x) use ($multiplier) {
    return $x * $multiplier;
};

$callback = function($item) {
    return strtoupper($item);
};

// ============================================================================
// Named Arguments (PHP 8.0+)
// ============================================================================

function createUser(string $name, string $email, int $age = 18): User
{
    return new User($name, $email, $age);
}

$user = createUser(
    name: 'Alice',
    email: 'alice@example.com',
    age: 25,
);

// ============================================================================
// Constructor Property Promotion (PHP 8.0+)
// ============================================================================

class Product
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly float $price,
        private int $stock = 0,
    ) {}
}

// ============================================================================
// Readonly Properties and Classes (PHP 8.1+, 8.2+)
// ============================================================================

readonly class Configuration
{
    public function __construct(
        public string $apiKey,
        public string $apiSecret,
        public bool $debug = false,
    ) {}
}

class Setting
{
    public readonly string $key;
    public readonly mixed $value;
}

// ============================================================================
// Union and Intersection Types (PHP 8.0+, 8.1+)
// ============================================================================

function process(int|float|string $value): int|float
{
    return is_numeric($value) ? (int) $value : strlen($value);
}

interface Loggable {}
interface Timestampable {}

function logEntity(Loggable&Timestampable $entity): void
{
    // Process entity
}

// ============================================================================
// Null Safe Operator (PHP 8.0+)
// ============================================================================

$username = $user?->profile?->getName();
$email = $account?->getUser()?->email;

// ============================================================================
// First-class Callable Syntax (PHP 8.1+)
// ============================================================================

$strlen = strlen(...);
$arrayMap = array_map(...);
$userMethod = $user->getName(...);
$staticMethod = UserRepository::findById(...);

// ============================================================================
// String interpolation and heredoc/nowdoc
// ============================================================================

$name = 'World';
$simple = "Hello, $name!";
$complex = "User: {$user->getName()}";
$expression = "Result: {$a + $b}";

$heredoc = <<<HTML
<div class="user">
    <h1>$name</h1>
    <p>{$user->getEmail()}</p>
</div>
HTML;

$nowdoc = <<<'TEXT'
This is raw text: $name {$user}
No interpolation here!
TEXT;

// ============================================================================
// Generators (PHP 5.5+)
// ============================================================================

function generateNumbers(int $max): \Generator
{
    for ($i = 0; $i < $max; $i++) {
        yield $i;
    }
}

function fibonacci(): \Generator
{
    $a = 0;
    $b = 1;
    while (true) {
        yield $a;
        [$a, $b] = [$b, $a + $b];
    }
}

// ============================================================================
// Traits (PHP 5.4+)
// ============================================================================

trait Timestampable
{
    protected ?\DateTimeImmutable $createdAt = null;

    public function touch(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }
}

class Post
{
    use Timestampable;

    public function __construct(public string $title) {}
}

// ============================================================================
// Anonymous Classes (PHP 7.0+)
// ============================================================================

$logger = new class implements LoggerInterface {
    public function log($level, $message, array $context = []): void
    {
        echo "[$level] $message\n";
    }

    // Other PSR-3 methods...
    public function emergency($message, array $context = []): void {}
    public function alert($message, array $context = []): void {}
    public function critical($message, array $context = []): void {}
    public function error($message, array $context = []): void {}
    public function warning($message, array $context = []): void {}
    public function notice($message, array $context = []): void {}
    public function info($message, array $context = []): void {}
    public function debug($message, array $context = []): void {}
};

// ============================================================================
// Array Destructuring (PHP 7.1+)
// ============================================================================

[$a, $b, $c] = [1, 2, 3];
['name' => $name, 'age' => $age] = $userData;

// ============================================================================
// Spread Operator (PHP 5.6+ for arguments, 7.4+ for arrays)
// ============================================================================

function sum(int ...$numbers): int
{
    return array_sum($numbers);
}

$total = sum(1, 2, 3, 4, 5);
$merged = [...$array1, ...$array2, ...$array3];

// ============================================================================
// Comments
// ============================================================================

// Single-line comment
/* Multi-line
   comment */
/** DocBlock comment */
# Hash-style comment

// ============================================================================
// Complex Expressions
// ============================================================================

$result = $users
    |> array_filter(..., fn($u) => $u->active)
    |> array_map(..., fn($u) => [
        'id' => $u->id,
        'name' => $u->getName(),
        'email' => $u->email,
    ])
    |> array_values(...);

$chain = $object
    ?->method1()
    ?->method2()
    ?->property
    ?? 'default';
