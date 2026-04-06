<?php

declare(strict_types=1);

/*
 * This file is part of the ALTO library.
 *
 * © 2026–present Simon André
 *
 * For full copyright and license information, please see
 * the LICENSE file distributed with this source code.
 */

// Adjacent whitespace tokens - exercises PhpLexer::normalize()
$var = 'multiple   spaces';
$x = 'tabs  and  spaces';

// Class definition vs instantiation
class User
{
    public function __construct(public string $name)
    {
    }
}
$user = new User('Alice');

// Function definition vs call
function calculate($a, $b)
{
    return $a + $b;
}
$result = calculate(1, 2);

// Method definition vs call
class Math
{
    public static function add($x, $y)
    {
        return $x + $y;
    }
}
$sum = Math::add(3, 4);

// Variable variations
$simple = 123;
$$dynamic = 'dynamic';
$array['key'] = 'value';

// Constants
const GLOBAL_CONST = 'global';
define('DEFINED_CONST', 'defined');
echo GLOBAL_CONST;
echo DEFINED_CONST;

// Namespaces and use statements

namespace App\Service;

// Attributes
#[Route('/api')]
class ApiController
{
    #[Get('/users')]
    public function getUsers(): array
    {
        return [];
    }
}

// Arrow functions and closures
$arrow = fn ($x) => $x * 2;
$closure = function ($y) { return $y + 1; };

// Match expression
$type = match ($value) {
    1 => 'one',
    2 => 'two',
    default => 'other',
};

// Heredoc and nowdoc
$heredoc = <<<EOT
Heredoc text
EOT;

$nowdoc = <<<'EOT'
Nowdoc text
EOT;

// Enums
enum Status
{
    case Active;
    case Inactive;
}

// Interface and trait
interface Loggable
{
    public function log(string $message): void;
}

trait Timestampable
{
    public function getTimestamp(): int
    {
        return time();
    }
}

// Various operators
$a = 1 + 2 - 3 * 4 / 5;
$b = true && false || !true;
$c = 'hello world';
$d = $x ?? 'default';
$e = $y ?: 'fallback';
$f = $a <=> $b;

// Comments variations
// Single line comment
/* Multi-line
   comment */
/** Doc comment */
// Hash comment

// String interpolation
$name = 'World';
$greeting = "Hello, $name!";
$complex = "Result: {$obj->method()}";
