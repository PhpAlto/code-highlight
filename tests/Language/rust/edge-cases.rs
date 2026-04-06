// Edge cases for Rust semantic parser coverage

// Inner attributes with #!
#![allow(unused)]
#![feature(test)]
#![no_std]

// Nested and complex attributes
#[derive(Debug, Clone)]
#[cfg(all(unix, target_arch = "x86_64"))]
struct AttributeTest;

// Attribute on module
#[cfg(test)]
mod test_module {
    use super::*;
}

// Module definitions (ExpectingModuleName state)
mod my_module;
mod nested {
    mod inner {}
}
pub mod public_mod;

// Constants with ALL_CAPS pattern (regex test)
const MAX_SIZE: usize = 1024;
const DEFAULT_PORT: u16 = 8080;
const API_VERSION: &str = "v1";
static GLOBAL_COUNTER: i32 = 0;
static mut MUTABLE_GLOBAL: bool = false;

// Type references - CamelCase starting with uppercase
struct MyStruct;
enum MyEnum {}
trait MyTrait {}
type MyType = String;
union MyUnion { x: i32, y: f32 }

// Builtin types
fn builtin_types() {
    let b: bool = true;
    let c: char = 'x';
    let f32_val: f32 = 1.0;
    let f64_val: f64 = 2.0;
    let i8_val: i8 = 1;
    let i16_val: i16 = 2;
    let i32_val: i32 = 3;
    let i64_val: i64 = 4;
    let i128_val: i128 = 5;
    let isize_val: isize = 6;
    let u8_val: u8 = 7;
    let u16_val: u16 = 8;
    let u32_val: u32 = 9;
    let u64_val: u64 = 10;
    let u128_val: u128 = 11;
    let usize_val: usize = 12;
    let str_val: &str = "test";
    let string_val: String = String::new();
    let vec_val: Vec<i32> = Vec::new();
    let opt_val: Option<i32> = None;
    let res_val: Result<i32, String> = Ok(1);
}

// Builtin functions
fn builtin_funcs() {
    drop(value);
    panic!("error");
    todo!();
    unimplemented!();
    unreachable!();
    assert!(true);
    assert_eq!(1, 1);
    assert_ne!(1, 2);
    debug_assert!(true);
    dbg!(value);
    eprintln!("error");
    print!("msg");
    println!("msg");
    format!("{}",  arg);
    vec![1, 2, 3];
}

// Function definitions vs calls
fn my_function() {}
fn another_func() {
    my_function();  // Call
    another_func(); // Call
}

// Impl keyword (ExpectingImplType state)
impl MyTrait for MyStruct {}
impl MyStruct {
    fn method(&self) {}
}
impl<T> MyTrait for T {}

// State stack edge cases - nested braces
fn nested_braces() {
    {
        {
            {
                let x = 1;
            }
        }
    }
}

// Attribute depth tracking
#[outer(inner(deep))]
struct NestedAttr;

// Self type reference
impl MyStruct {
    fn returns_self() -> Self {
        Self
    }
}

// Edge case: function followed by non-( punctuation
fn edge_case() -> i32 { 42 }
let x = edge_case;  // Not a call

// Constants vs variables
let lowercase = 1;  // Variable
const UPPERCASE: i32 = 2;  // Constant
