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

$string = 'hello';
$number = 42;
$float = 3.14;
$bool = true;
$null = null;

// Basic function definition
function greet($name)
{
    return "Hello, $name!";
}

// Function call
$message = greet('World');

// Basic class
class User
{
    public string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }
}

// Class instantiation
$user = new User('Alice');
$userName = $user->getName();

// Basic array
$array = [1, 2, 3, 4, 5];
$assoc = ['key' => 'value', 'foo' => 'bar'];

// Control structures
if ($bool) {
    echo 'true';
} else {
    echo 'false';
}

foreach ($array as $item) {
    echo $item;
}

while ($number > 0) {
    --$number;
}

// Basic operators
$sum = 1 + 2;
$product = 3 * 4;
$concat = 'hello world';
$comparison = 3 === $sum;
$logical = true && false;
