// Comprehensive JavaScript ES6+ Test - All Features

// ============================================================================
// Comments
// ============================================================================

// Single-line comment

/*
 * Multi-line comment
 * with multiple lines
 */

/** JSDoc comment */

// ============================================================================
// Variables and Constants
// ============================================================================

var oldStyle = 'var keyword';
let modernVariable = 'let keyword';
const CONSTANT_VALUE = 'const keyword';

// ============================================================================
// Primitives
// ============================================================================

const string1 = 'single quotes';
const string2 = "double quotes";
const templateLiteral = `template literal`;
const templateWithExpression = `value: ${CONSTANT_VALUE}`;
const multilineTemplate = `
  Line 1
  Line 2
  Line 3
`;

const intNumber = 42;
const floatNumber = 3.14159;
const scientificNotation = 1.23e10;
const hexNumber = 0xFF;
const binaryNumber = 0b1010;
const octalNumber = 0o755;
const bigInt = 123456789012345678901234567890n;

const boolTrue = true;
const boolFalse = false;

const nullValue = null;
const undefinedValue = undefined;

// ============================================================================
// Regular Expressions
// ============================================================================

const simpleRegex = /pattern/;
const regexWithFlags = /pattern/gi;
const complexRegex = /^[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
const regexWithEscape = /\d+\.\d+/;
const regexCharClass = /[^abc]/;

// ============================================================================
// Operators
// ============================================================================

// Arithmetic
const add = 1 + 2;
const subtract = 5 - 3;
const multiply = 4 * 6;
const divide = 10 / 2;
const modulo = 10 % 3;
const exponent = 2 ** 8;

// Assignment
let x = 10;
x += 5;
x -= 3;
x *= 2;
x /= 4;
x %= 3;
x **= 2;

// Comparison
const equal = 1 == '1';
const strictEqual = 1 === 1;
const notEqual = 1 != 2;
const strictNotEqual = 1 !== '1';
const lessThan = 1 < 2;
const lessOrEqual = 1 <= 2;
const greaterThan = 2 > 1;
const greaterOrEqual = 2 >= 1;

// Logical
const and = true && false;
const or = true || false;
const not = !true;

// Bitwise
const bitwiseAnd = 5 & 3;
const bitwiseOr = 5 | 3;
const bitwiseXor = 5 ^ 3;
const bitwiseNot = ~5;
const leftShift = 5 << 1;
const rightShift = 5 >> 1;
const unsignedRightShift = -5 >>> 1;

// Nullish coalescing
const nullish = null ?? 'default';

// Optional chaining
const optional = obj?.prop?.nested;

// ============================================================================
// Arrays
// ============================================================================

const emptyArray = [];
const numberArray = [1, 2, 3, 4, 5];
const mixedArray = [1, 'two', true, null, {key: 'value'}];
const nestedArray = [[1, 2], [3, 4], [5, 6]];

// Array destructuring
const [first, second, ...rest] = numberArray;
const [a, , c] = [1, 2, 3];

// Spread operator
const combined = [...numberArray, ...mixedArray];
const copied = [...numberArray];

// ============================================================================
// Objects
// ============================================================================

const emptyObject = {};
const simpleObject = {
  key: 'value',
  number: 42,
  nested: {
    deep: true
  }
};

// Shorthand property
const name = 'John';
const age = 30;
const person = {name, age};

// Computed property names
const propName = 'dynamicKey';
const computed = {
  [propName]: 'value',
  [`${propName}2`]: 'value2'
};

// Method shorthand
const methods = {
  method() {
    return 'shorthand';
  },
  async asyncMethod() {
    return 'async';
  }
};

// Object destructuring
const {key, number} = simpleObject;
const {nested: {deep}} = simpleObject;
const {missing = 'default'} = simpleObject;

// Spread in objects
const extended = {...simpleObject, extra: 'field'};

// ============================================================================
// Functions
// ============================================================================

// Traditional function
function traditionalFunction(param1, param2) {
  return param1 + param2;
}

// Function expression
const functionExpression = function(x) {
  return x * 2;
};

// Arrow functions
const arrowFunction = () => 'simple';
const arrowWithParam = (x) => x * 2;
const arrowMultiParam = (x, y) => x + y;
const arrowWithBlock = (x) => {
  const result = x * 2;
  return result;
};

// Default parameters
function withDefaults(a = 1, b = 2) {
  return a + b;
}

// Rest parameters
function restParams(...args) {
  return args.reduce((sum, n) => sum + n, 0);
}

// Destructuring parameters
function destructureParams({name, age}) {
  return `${name} is ${age}`;
}

// Async function
async function asyncFunction() {
  const result = await Promise.resolve('done');
  return result;
}

// Async arrow function
const asyncArrow = async () => {
  const data = await fetch('/api/data');
  return data.json();
};

// Generator function
function* generatorFunction() {
  yield 1;
  yield 2;
  yield 3;
}

// ============================================================================
// Classes
// ============================================================================

class BaseClass {
  constructor(name) {
    this.name = name;
  }

  method() {
    return `Hello, ${this.name}`;
  }

  static staticMethod() {
    return 'Static method';
  }

  get getter() {
    return this._value;
  }

  set setter(value) {
    this._value = value;
  }
}

class DerivedClass extends BaseClass {
  constructor(name, age) {
    super(name);
    this.age = age;
  }

  method() {
    return `${super.method()}, age ${this.age}`;
  }
}

// Class expression
const ClassExpression = class {
  constructor() {
    this.type = 'expression';
  }
};

// ============================================================================
// Modules (ES6 import/export)
// ============================================================================

// Named exports
export const exportedConst = 'value';
export function exportedFunction() {
  return 'exported';
}
export class ExportedClass {}

// Default export
export default class DefaultExport {
  constructor() {
    this.isDefault = true;
  }
}

// Re-export
export {something} from './other-module';
export * from './all-exports';

// Named imports
import {namedImport} from './module';
import {original as renamed} from './module';
import * as namespace from './module';

// Default import
import defaultImport from './module';

// Mixed imports
import defaultImport2, {named1, named2} from './module';

// Dynamic import
const dynamicModule = await import('./dynamic-module.js');

// ============================================================================
// Control Flow
// ============================================================================

// If-else
if (condition) {
  console.log('true');
} else if (otherCondition) {
  console.log('other');
} else {
  console.log('false');
}

// Ternary
const result = condition ? 'yes' : 'no';

// Switch
switch (value) {
  case 1:
    console.log('one');
    break;
  case 2:
  case 3:
    console.log('two or three');
    break;
  default:
    console.log('other');
}

// For loop
for (let i = 0; i < 10; i++) {
  console.log(i);
}

// For-in loop
for (const key in object) {
  console.log(key);
}

// For-of loop
for (const item of array) {
  console.log(item);
}

// While loop
while (condition) {
  // do something
}

// Do-while loop
do {
  // do something
} while (condition);

// ============================================================================
// Error Handling
// ============================================================================

try {
  riskyOperation();
} catch (error) {
  console.error(error);
} finally {
  cleanup();
}

// ============================================================================
// Promises
// ============================================================================

const promise = new Promise((resolve, reject) => {
  if (success) {
    resolve('Success!');
  } else {
    reject('Failed!');
  }
});

promise
  .then(result => console.log(result))
  .catch(error => console.error(error))
  .finally(() => console.log('Done'));

// Promise methods
Promise.all([promise1, promise2, promise3]);
Promise.race([promise1, promise2]);
Promise.allSettled([promise1, promise2]);
Promise.any([promise1, promise2]);

// ============================================================================
// Advanced Features
// ============================================================================

// Symbols
const symbol = Symbol('description');
const symbolFor = Symbol.for('global');

// Proxy
const proxy = new Proxy(target, {
  get(target, property) {
    return property in target ? target[property] : 'default';
  }
});

// Reflect
const hasProperty = Reflect.has(object, 'key');
const ownKeys = Reflect.ownKeys(object);

// Map
const map = new Map();
map.set('key1', 'value1');
map.set('key2', 'value2');

// Set
const set = new Set([1, 2, 3, 3, 4]);

// WeakMap and WeakSet
const weakMap = new WeakMap();
const weakSet = new WeakSet();

// ============================================================================
// typeof and instanceof
// ============================================================================

const typeCheck = typeof variable;
const instanceCheck = object instanceof Class;

// ============================================================================
// this keyword
// ============================================================================

const obj = {
  method: function() {
    return this;
  },
  arrow: () => this
};

// ============================================================================
// new keyword
// ============================================================================

const instance = new MyClass();
const date = new Date();
const error = new Error('message');

// ============================================================================
// Getter/Setter (outside class)
// ============================================================================

const objectWithGetSet = {
  _value: 0,
  get value() {
    return this._value;
  },
  set value(v) {
    this._value = v;
  }
};

// ============================================================================
// Destructuring edge cases
// ============================================================================

const {a: aliased, b = 'default'} = obj;
const [...spread] = array;
const {0: firstElem} = array;

// ============================================================================
// Template Literal Advanced
// ============================================================================

function tag(strings, ...values) {
  return strings.reduce((result, str, i) => {
    return result + str + (values[i] || '');
  }, '');
}

const tagged = tag`Hello ${name}, you are ${age} years old`;

// ============================================================================
// Complex Expressions
// ============================================================================

const complex = ((a, b) => a + b)(1, 2);
const iife = (function() { return 'IIFE'; })();
const asyncIIFE = (async () => { return await Promise.resolve('async'); })();
