// Line comment
/* Block comment */
package main

import "fmt"

// Boolean literals and nil
var isTrue bool = true
var isFalse bool = false
var ptr *int = nil

// Numbers - decimal, hex, binary, octal
var dec = 42
var hex = 0xFF
var bin = 0b1010
var oct = 0o755

// Float with decimal and exponent
var float1 = 3.14
var float2 = 1.5e10

// Imaginary number
var img = 5i

// Raw string
var raw = `Multi
line
raw string`

// Regular string with escapes
var str = "Hello \"world\""

// Rune literal
var r = 'A'

// Operators - three char
x <<= 1
y >>= 2
z &^= 3

// Operators - two char
a == b
c != d
e <= f
g >= h
i && j
k || l
m++
n--
o << 1
p >> 2
q += 1
r -= 1
s *= 2
t /= 2
u %= 3
v &= 4
w |= 5
x ^= 6
y &^ z

// Operators - single char
+ - * / % = < > ! & | ^

// Short variable declaration
foo := 10

// Variadic / spread
func varargs(nums ...int) {}

// Channel receive
ch <- val
<-ch

// Punctuation
func test() {
	arr[0] = val
	obj.field = value
}

// Keywords
type MyStruct struct{}
const PI = 3.14
var global int
func myFunc() {}
if condition {}
else {}
for i := range items {}
switch val {
case 1:
	break
default:
	continue
}
go routine()
defer cleanup()
return result
select {
case <-ch:
}
interface MyInterface {}
map[string]int{}
chan int
goto label
fallthrough
