// Line comment
/* Block comment */
//! Outer doc comment
/*! Outer doc block */
/// Inner doc comment
/** Inner doc block */

// Boolean and unit
let is_true: bool = true;
let is_false: bool = false;

// Numbers - decimal, hex, binary, octal, float
let dec = 42;
let hex = 0xFF;
let bin = 0b1010;
let oct = 0o755;
let flt = 3.14;
let exp = 1.5e10;
let suffix_i32 = 42i32;
let suffix_f64 = 3.14f64;
let underscore = 1_000_000;

// Strings and chars
let str1 = "Double quoted";
let raw = r"Raw string";
let raw_hash = r#"Raw with # "quotes" #"#;
let byte_str = b"Bytes";
let char_lit = 'A';
let byte_lit = b'A';

// Two-char operators
a == b;
c != d;
e <= f;
g >= h;
i && j;
k || l;
m .. n;
o ..= p;
q => r;
s -> t;
u :: v;
w += 1;
x -= 1;
y *= 2;
z /= 2;
aa %= 3;
bb &= 4;
cc |= 5;
dd ^= 6;
ee <<= 1;
ff >>= 1;

// Single-char operators
+ - * / % = < > ! & | ^ ~ @ . ?

// Punctuation
func(arg);
arr[0];
obj.field;
{block}
(a, b);
:

// Keywords and patterns
fn function(param: i32) -> i32 {
    if condition {
        return result;
    } else if other {
        0
    } else {
        1
    }

    let x = match value {
        0 => "zero",
        1..=9 => "small",
        _ => "large",
    };

    for item in items {
        break;
        continue;
    }

    while condition {
        loop {
            break 'outer;
        }
    }

    loop {
        if done { break; }
    }

    let result = 'label: {
        break 'label 42;
    };

    struct MyStruct {
        field: i32,
    }

    enum MyEnum {
        Variant1,
        Variant2(i32),
        Variant3 { x: i32 },
    }

    trait MyTrait {
        fn method(&self);
    }

    impl MyTrait for MyStruct {
        fn method(&self) {}
    }

    type Alias = Vec<i32>;

    const CONST: i32 = 42;
    static STATIC: i32 = 42;
    static mut MUT_STATIC: i32 = 42;

    let mut mutable = 0;
    let ref reference = value;
    let ref mut mut_ref = value;

    unsafe {
        let raw_ptr: *const i32 = &x;
        let raw_mut: *mut i32 = &mut y;
    }

    extern "C" fn c_func() {}
    extern crate other;

    use std::collections::HashMap;
    use self::module;
    use super::parent;

    mod module {
        pub fn public() {}
        pub(crate) fn crate_vis() {}
        pub(super) fn parent_vis() {}
    }

    async fn async_func() {
        await!(future);
    }

    move || closure;
    where T: Trait;
    dyn Trait;
    impl Trait for Type {}
    union Union { x: i32 }

    let _ = value;
    let Some(x) = option else { return };

    macro_rules! my_macro {
        () => {};
    }

    #[derive(Debug)]
    #[cfg(test)]
    struct Attributed;
}
