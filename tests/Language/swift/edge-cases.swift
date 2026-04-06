// Edge cases for Swift coverage

// Nested block comments
/* Outer /* Inner /* Deep */ Inner */ Outer */
/** Doc /** Nested */ Doc */

// Multiline strings with interpolation
let msg = """
Value: \(x + y)
Name: \(user.name)
"""

// Backtick identifiers (keywords as names)
let `class` = "reserved"
let `init` = 42
let `func` = true

// Hex floats
let hexFloat = 0xAp3
let hexFloat2 = 0xF.8p-2

// Three-char operators
let range = 1...10
let halfRange = 0..<5
let assign = (a ??= b)
let leftShift = (x <<= 2)
let rightShift = (y >>= 1)
let identical = (obj1 === obj2)
let notIdentical = (obj1 !== obj2)

// Attributes with arguments
@available(iOS 15.0, macOS 12.0, *)
@discardableResult
@MainActor
@resultBuilder
func builder() {}

// Compiler directives
#if os(iOS)
  print("iOS")
#elseif os(macOS)
  print("macOS")
#else
  print("Other")
#endif

#available(iOS 15.0, *)
#selector(method)
#file
#line
#column
#function

// String interpolation edge cases
let complex = "Result: \(calc(a: 1, b: 2))"
let nested = "\(outer("\(inner)"))"
let escaped = "Quote: \" Backslash: \\ Newline: \n"

// Numbers with underscores
let big = 1_000_000
let hex = 0xFF_FF_FF
let bin = 0b1010_1010
let oct = 0o755_755

// Float exponent with signs
let expPos = 1.5e+10
let expNeg = 2.3e-5
