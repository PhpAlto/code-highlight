// Comprehensive TypeScript Test - All Features

// ============================================================================
// Type Annotations
// ============================================================================

let stringVar: string = 'hello';
let numberVar: number = 42;
let booleanVar: boolean = true;
let anyVar: any = 'anything';
let unknownVar: unknown = 'unknown';
let neverVar: never;
let voidFunc: void;
let nullVar: null = null;
let undefinedVar: undefined = undefined;
let bigIntVar: bigint = 100n;
let symbolVar: symbol = Symbol('sym');

// ============================================================================
// Arrays and Tuples
// ============================================================================

let numberArray: number[] = [1, 2, 3];
let stringArray: Array<string> = ['a', 'b', 'c'];
let mixedTuple: [string, number, boolean] = ['text', 42, true];
let readonlyArray: readonly number[] = [1, 2, 3];

// ============================================================================
// Interfaces
// ============================================================================

interface Person {
  name: string;
  age: number;
  email?: string;
  readonly id: number;
}

interface Employee extends Person {
  department: string;
  salary: number;
}

interface Callable {
  (x: number): number;
}

interface Indexable {
  [key: string]: any;
}

interface Generic<T> {
  value: T;
  getValue(): T;
}

// ============================================================================
// Type Aliases
// ============================================================================

type StringOrNumber = string | number;
type Callback = (data: string) => void;
type Point = {
  x: number;
  y: number;
};

type Nullable<T> = T | null;
type ReadonlyPoint = Readonly<Point>;
type PartialPerson = Partial<Person>;
type RequiredPerson = Required<Person>;
type PickedPerson = Pick<Person, 'name' | 'age'>;
type OmittedPerson = Omit<Person, 'email'>;

// ============================================================================
// Union and Intersection Types
// ============================================================================

type ID = string | number;
type Status = 'pending' | 'approved' | 'rejected';

type Named = { name: string };
type Aged = { age: number };
type NamedAndAged = Named & Aged;

// ============================================================================
// Enums
// ============================================================================

enum Direction {
  Up,
  Down,
  Left,
  Right,
}

enum Color {
  Red = 'RED',
  Green = 'GREEN',
  Blue = 'BLUE',
}

enum FileAccess {
  None = 0,
  Read = 1 << 0,
  Write = 1 << 1,
  ReadWrite = Read | Write,
}

const enum ConstEnum {
  A,
  B,
  C,
}

// ============================================================================
// Functions with Type Annotations
// ============================================================================

function add(a: number, b: number): number {
  return a + b;
}

function greet(name: string, greeting: string = 'Hello'): string {
  return `${greeting}, ${name}`;
}

function optional(required: string, optional?: number): void {
  console.log(required, optional);
}

function rest(...numbers: number[]): number {
  return numbers.reduce((sum, n) => sum + n, 0);
}

const arrowTyped = (x: number): number => x * 2;

const arrowWithGenerics = <T>(value: T): T => value;

// ============================================================================
// Generics
// ============================================================================

function identity<T>(arg: T): T {
  return arg;
}

function mapArray<T, U>(array: T[], fn: (item: T) => U): U[] {
  return array.map(fn);
}

class GenericClass<T> {
  private value: T;

  constructor(value: T) {
    this.value = value;
  }

  getValue(): T {
    return this.value;
  }
}

interface GenericInterface<T, U> {
  first: T;
  second: U;
}

// Generic constraints
function longest<T extends { length: number }>(a: T, b: T): T {
  return a.length >= b.length ? a : b;
}

// ============================================================================
// Classes with TypeScript Features
// ============================================================================

class Animal {
  protected name: string;

  constructor(name: string) {
    this.name = name;
  }

  public move(distance: number = 0): void {
    console.log(`${this.name} moved ${distance}m.`);
  }
}

class Dog extends Animal {
  private breed: string;

  constructor(name: string, breed: string) {
    super(name);
    this.breed = breed;
  }

  public bark(): void {
    console.log('Woof! Woof!');
  }
}

// Abstract class
abstract class Shape {
  abstract getArea(): number;

  public describe(): string {
    return `Area: ${this.getArea()}`;
  }
}

class Circle extends Shape {
  constructor(private radius: number) {
    super();
  }

  getArea(): number {
    return Math.PI * this.radius ** 2;
  }
}

// Class with access modifiers
class BankAccount {
  public readonly accountNumber: string;
  private balance: number;
  protected owner: string;

  constructor(accountNumber: string, initialBalance: number, owner: string) {
    this.accountNumber = accountNumber;
    this.balance = initialBalance;
    this.owner = owner;
  }

  public deposit(amount: number): void {
    this.balance += amount;
  }

  private calculateInterest(): number {
    return this.balance * 0.05;
  }
}

// Class with static members
class MathUtils {
  static PI: number = 3.14159;

  static circleArea(radius: number): number {
    return this.PI * radius ** 2;
  }
}

// ============================================================================
// Decorators
// ============================================================================

function sealed(constructor: Function) {
  Object.seal(constructor);
  Object.seal(constructor.prototype);
}

function logMethod(target: any, propertyKey: string, descriptor: PropertyDescriptor) {
  const originalMethod = descriptor.value;
  descriptor.value = function (...args: any[]) {
    console.log(`Calling ${propertyKey} with args:`, args);
    return originalMethod.apply(this, args);
  };
  return descriptor;
}

@sealed
class DecoratedClass {
  @logMethod
  someMethod(arg: string): void {
    console.log(arg);
  }
}

// ============================================================================
// Type Assertions
// ============================================================================

let someValue: unknown = 'this is a string';
let strLength1: number = (someValue as string).length;
let strLength2: number = (<string>someValue).length;

// Non-null assertion
let maybeString: string | null = 'hello';
let definitelyString: string = maybeString!;

// ============================================================================
// Type Guards
// ============================================================================

function isString(value: unknown): value is string {
  return typeof value === 'string';
}

function isNumber(value: unknown): value is number {
  return typeof value === 'number';
}

function processValue(value: string | number) {
  if (typeof value === 'string') {
    return value.toUpperCase();
  } else {
    return value.toFixed(2);
  }
}

// ============================================================================
// Conditional Types
// ============================================================================

type IsString<T> = T extends string ? true : false;
type ExtractString<T> = T extends string ? T : never;

type TypeName<T> = T extends string
  ? 'string'
  : T extends number
  ? 'number'
  : T extends boolean
  ? 'boolean'
  : 'object';

// ============================================================================
// Mapped Types
// ============================================================================

type Optional<T> = {
  [P in keyof T]?: T[P];
};

type ReadonlyType<T> = {
  readonly [P in keyof T]: T[P];
};

type Getters<T> = {
  [K in keyof T as `get${Capitalize<string & K>}`]: () => T[K];
};

// ============================================================================
// Utility Types
// ============================================================================

type PersonPartial = Partial<Person>;
type PersonRequired = Required<Person>;
type PersonReadonly = Readonly<Person>;
type PersonRecord = Record<string, Person>;

type PersonNameAge = Pick<Person, 'name' | 'age'>;
type PersonWithoutEmail = Omit<Person, 'email'>;

type ExtractNumbers = Extract<string | number | boolean, number>;
type ExcludeNumbers = Exclude<string | number | boolean, number>;

type NonNullableString = NonNullable<string | null | undefined>;

type ReturnTypeExample = ReturnType<typeof add>;
type ParametersExample = Parameters<typeof add>;

// ============================================================================
// Template Literal Types
// ============================================================================

type EventNames = 'click' | 'scroll' | 'mousemove';
type EventHandlers = `on${Capitalize<EventNames>}`;

type PropEventSource<T> = {
  on<K extends string & keyof T>(eventName: `${K}Changed`, callback: (newValue: T[K]) => void): void;
};

// ============================================================================
// Namespaces
// ============================================================================

namespace Validation {
  export interface StringValidator {
    isValid(s: string): boolean;
  }

  export class EmailValidator implements StringValidator {
    isValid(s: string): boolean {
      return s.includes('@');
    }
  }

  export function validateEmail(email: string): boolean {
    const validator = new EmailValidator();
    return validator.isValid(email);
  }
}

// ============================================================================
// Modules
// ============================================================================

export type ExportedType = {
  id: number;
  name: string;
};

export interface ExportedInterface {
  method(): void;
}

export class ExportedClass {
  constructor(public value: string) {}
}

export const exportedConst: number = 42;

export function exportedFunction(param: string): string {
  return param.toUpperCase();
}

// Default export
export default class DefaultClass {
  private data: string;

  constructor(data: string) {
    this.data = data;
  }
}

// Re-exports
export { Something } from './other-module';
export * from './all-exports';

// ============================================================================
// Async/Await with Types
// ============================================================================

async function fetchData(url: string): Promise<Response> {
  const response = await fetch(url);
  return response;
}

async function getData<T>(url: string): Promise<T> {
  const response = await fetch(url);
  const data: T = await response.json();
  return data;
}

const asyncArrow = async (id: number): Promise<string> => {
  const result = await fetchData(`/api/items/${id}`);
  return result.text();
};

// ============================================================================
// Index Signatures
// ============================================================================

interface StringDictionary {
  [key: string]: string;
}

interface NumberDictionary {
  [index: number]: string;
}

interface MixedDictionary {
  [key: string]: string | number;
  length: number;
}

// ============================================================================
// This Type
// ============================================================================

interface Counter {
  count: number;
  increment(this: Counter): void;
}

const counter: Counter = {
  count: 0,
  increment(this: Counter) {
    this.count++;
  },
};

// ============================================================================
// Readonly Arrays and Tuples
// ============================================================================

const readonlyNumbers: readonly number[] = [1, 2, 3];
const readonlyTuple: readonly [string, number] = ['hello', 42];

// ============================================================================
// Const Assertions
// ============================================================================

const literalString = 'hello' as const;
const literalObject = {
  name: 'John',
  age: 30,
} as const;

const literalArray = [1, 2, 3] as const;

// ============================================================================
// Discriminated Unions
// ============================================================================

type SuccessResponse = {
  type: 'success';
  data: string;
};

type ErrorResponse = {
  type: 'error';
  message: string;
};

type Response = SuccessResponse | ErrorResponse;

function handleResponse(response: Response) {
  if (response.type === 'success') {
    console.log(response.data);
  } else {
    console.error(response.message);
  }
}

// ============================================================================
// Advanced Generics
// ============================================================================

function merge<T, U>(obj1: T, obj2: U): T & U {
  return { ...obj1, ...obj2 };
}

type Constructor<T = {}> = new (...args: any[]) => T;

function Timestamped<TBase extends Constructor>(Base: TBase) {
  return class extends Base {
    timestamp = Date.now();
  };
}

// ============================================================================
// Keyof and Typeof
// ============================================================================

interface PersonType {
  name: string;
  age: number;
}

type PersonKeys = keyof PersonType; // 'name' | 'age'

const person = {
  name: 'John',
  age: 30,
};

type PersonTypeof = typeof person;

// ============================================================================
// Infer Keyword
// ============================================================================

type ReturnTypeCustom<T> = T extends (...args: any[]) => infer R ? R : never;
type UnwrapPromise<T> = T extends Promise<infer U> ? U : T;

// ============================================================================
// Triple-Slash Directives
// ============================================================================

/// <reference path="./declarations.d.ts" />
/// <reference types="node" />

// ============================================================================
// Complex Type Combinations
// ============================================================================

type DeepReadonly<T> = {
  readonly [P in keyof T]: T[P] extends object ? DeepReadonly<T[P]> : T[P];
};

type DeepPartial<T> = {
  [P in keyof T]?: T[P] extends object ? DeepPartial<T[P]> : T[P];
};

// ============================================================================
// JSDoc with TypeScript
// ============================================================================

/**
 * Calculates the sum of two numbers
 * @param a - First number
 * @param b - Second number
 * @returns The sum of a and b
 */
function sum(a: number, b: number): number {
  return a + b;
}
