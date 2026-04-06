// More edge cases for Rust coverage

// Raw string literals
r"raw string"
r#"raw string with "quotes""#
r##"raw string with # and "quotes""##
r###"raw string with ## and "quotes""###

// Byte strings
b"byte string"
b"escape: \n \t \\"
br"raw byte string"
br#"raw byte string with "quotes""#

// Character literals
'a'
'\n'
'\t'
'\r'
'\\'
'\''
'\"'
'\0'
'\x7F'
'\u{1F600}'

// Byte literals
b'a'
b'\n'
b'\x7F'

// Numeric literals with suffixes
42i8
42i16
42i32
42i64
42i128
42isize
42u8
42u16
42u32
42u64
42u128
42usize
3.14f32
3.14f64

// Binary, octal, hex with underscores
0b1010_1010u8
0o755_755u32
0xFF_FF_FFu64
1_000_000i32
3.14_15_92f64

// Float special cases
1.0e10
1.0e-10
1.0E+10
1.0E-10
1.
.5
1.0
0.0

// Attributes on everything
#[test]
#[cfg(target_os = "linux")]
#[derive(Debug, Clone)]
#[allow(dead_code)]
#[warn(missing_docs)]
#[deny(unsafe_code)]
#[must_use]
#[repr(C)]
#[inline]
#[inline(always)]
#[cold]
#[no_mangle]
#[link_name = "foo"]
#[deprecated]
#[deprecated(since = "1.0.0", note = "use bar instead")]

// Lifetime annotations
fn lifetime<'a>(x: &'a str) -> &'a str {
    x
}

fn multiple_lifetimes<'a, 'b>(x: &'a str, y: &'b str) -> &'a str {
    x
}

struct WithLifetime<'a> {
    field: &'a str,
}

// Generic constraints
fn generic<T: Display + Debug>(t: T) {
    println!("{:?}", t);
}

fn where_clause<T>(t: T)
where
    T: Display + Debug + Clone,
{
    println!("{:?}", t);
}

// Associated types
trait MyTrait {
    type Item;
    type Error;

    fn method(&self) -> Self::Item;
}

// Turbofish syntax
vec.iter().collect::<Vec<_>>()
func::<i32, String>(arg)
Some::<i32>(42)

// Path segments
std::collections::HashMap
crate::module::Type
self::function()
super::parent_module::item
use std::io::{self, Read, Write};
use std::collections::*;

// Macro invocations
println!("hello")
vec![1, 2, 3]
format!("value: {}", x)
assert!(condition)
assert_eq!(a, b)
assert_ne!(a, b)
debug_assert!(condition)
panic!("error message")
unimplemented!()
unreachable!()
todo!()
dbg!(expression)
matches!(value, pattern)
concat!("a", "b")
stringify!(expr)
file!()
line!()
column!()
module_path!()
cfg!(feature = "foo")
env!("PATH")
option_env!("PATH")
include!("file.rs")
include_str!("file.txt")
include_bytes!("file.bin")

// Macro definitions
macro_rules! my_macro {
    () => { };
    ($x:expr) => { $x };
    ($x:expr, $y:expr) => { $x + $y };
}

// Pattern matching variants
match value {
    1 => "one",
    2 | 3 => "two or three",
    4..=10 => "four to ten",
    _ => "other",
}

match option {
    Some(x) => x,
    None => 0,
}

match result {
    Ok(v) => v,
    Err(e) => panic!("{}", e),
}

// Destructuring patterns
let (a, b, c) = (1, 2, 3);
let [first, second] = [1, 2];
let Point { x, y } = point;
let Point { x: new_x, y: new_y } = point;

// Reference patterns
match &value {
    &1 => "one",
    &2 => "two",
    _ => "other",
}

// If let and while let
if let Some(x) = option {
    println!("{}", x);
}

while let Some(x) = iter.next() {
    println!("{}", x);
}

// Closures with move
let closure = move || {
    value
};

let closure_args = move |x, y| {
    x + y
};

// Async/await
async fn async_function() -> Result<(), Error> {
    let result = async_operation().await?;
    Ok(())
}

// Trait bounds and impl Trait
fn function(arg: impl Display) {
    println!("{}", arg);
}

fn return_impl() -> impl Iterator<Item = i32> {
    vec![1, 2, 3].into_iter()
}

// Const and static
const MAX: usize = 100;
static GLOBAL: i32 = 42;
static mut MUTABLE_GLOBAL: i32 = 0;

// Unsafe blocks
unsafe {
    let ptr = addr as *const i32;
    *ptr
}

// FFI extern
extern "C" {
    fn c_function(arg: i32) -> i32;
}

extern "C" fn exported_function() {
    // exported to C
}

// Type aliases
type Result<T> = std::result::Result<T, Error>;
type BoxedFuture = Box<dyn Future<Output = ()>>;

// Visibility modifiers
pub struct Public;
pub(crate) struct CrateVisible;
pub(super) struct SuperVisible;
pub(in crate::module) struct PathVisible;

// Range expressions
1..10
1..=10
..10
1..
..

// Question mark operator
let value = may_fail()?;
let value = may_fail().await?;

// Field init shorthand
let x = 1;
let y = 2;
let point = Point { x, y };

// Struct update syntax
let point2 = Point { x: 3, ..point1 };

// Tuple struct
struct Tuple(i32, i32);
let tuple = Tuple(1, 2);

// Unit struct
struct Unit;
let unit = Unit;

// Enum with data
enum Message {
    Quit,
    Move { x: i32, y: i32 },
    Write(String),
    ChangeColor(i32, i32, i32),
}

// Match with guard
match value {
    x if x > 0 => "positive",
    x if x < 0 => "negative",
    _ => "zero",
}

// Slice patterns
match slice {
    [] => "empty",
    [x] => "one",
    [x, y] => "two",
    [first, .., last] => "many",
}

// Box patterns (experimental)
match boxed {
    box value => value,
}

// Ref patterns
match &value {
    ref x => {
        // x is &T
    }
}

// Range patterns
match value {
    0..=9 => "single digit",
    10..=99 => "double digit",
    _ => "other",
}

// Or patterns
match value {
    1 | 2 | 3 => "small",
    _ => "large",
}

// Cast expressions
let x = value as i32;
let ptr = addr as *const u8;

// Type ascription
let x: i32 = value;

// Dereference
let value = *ptr;
let value = **double_ptr;

// Reference
let ref_value = &value;
let mut_ref = &mut value;

// Method calls with turbofish
iter.collect::<Vec<_>>()
value.into::<String>()

// Associated constants
const ASSOC_CONST: i32 = Type::CONST;

// Self type
impl Type {
    fn new() -> Self {
        Self { }
    }
}

// Trait objects
let trait_object: &dyn Trait = &value;
let boxed: Box<dyn Trait> = Box::new(value);

// Higher-ranked trait bounds
fn hrtb<F>(f: F)
where
    F: for<'a> Fn(&'a str) -> &'a str,
{
}

// Label on loops
'outer: loop {
    'inner: loop {
        break 'outer;
    }
}

// Loop expressions
let value = loop {
    if condition {
        break result;
    }
};

// Documentation comments
/// Outer doc comment
pub fn function() {
    //! Inner doc comment
}

// Module-level doc
//! This is a crate-level doc comment

/** Block doc comment */
pub struct Documented;

// Complex nested generics
Vec<Vec<Vec<i32>>>
HashMap<String, Vec<Option<Result<i32, Error>>>>
Box<dyn Fn() -> Box<dyn Iterator<Item = i32>>>
