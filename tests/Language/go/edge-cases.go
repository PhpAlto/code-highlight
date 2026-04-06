// Edge cases for Go coverage
package main

// Built-in constants
const (
	First = iota
	Second
	Third
)

// Method with receiver - simple
type Greeter struct{}
func (g Greeter) Greet() string {
	return "Hello"
}

// Method with pointer receiver
func (g *Greeter) GreetPtr() string {
	return "Hi"
}

// Method with nested parentheses in receiver type (edge case)
func (g *((*Greeter))) NestedPtr() {}

// Function after keyword
type MyType int
func TypeFunc() {}

// Built-in functions
func test() {
	_ = append([]int{}, 1)
	_ = len("test")
	_ = cap(make([]int, 0, 10))
	_ = make(map[string]int)
	_ = new(int)
	panic("error")
	defer recover()
	print("msg")
	println("msg")
	_ = complex(1, 2)
	_ = real(1+2i)
	_ = imag(3+4i)
	copy([]int{}, []int{})
	close(make(chan int))
	delete(map[string]int{}, "key")
	clear(map[string]int{})
	_ = max(1, 2, 3)
	_ = min(4, 5, 6)
}

// Built-in types
var (
	b bool
	bt byte
	r rune
	i int
	i8 int8
	i16 int16
	i32 int32
	i64 int64
	u uint
	u8 uint8
	u16 uint16
	u32 uint32
	u64 uint64
	up uintptr
	f32 float32
	f64 float64
	c64 complex64
	c128 complex128
	s string
	e error
	a any
	comp comparable
)

// State transitions
func StateTest() {
	// Keywords that reset state
	if true {
		var x int
		const y = 1
		for range []int{} {}
		switch 1 {}
		go func() {}()
		defer func() {}()
		return
	}
}

// Nested receivers (edge case)
func NestedReceiverTest() {}

// Type keyword
type NewType = OldType
type AliasType OtherType

// Interface and struct keywords
type MyInterface interface {}
type MyStruct struct {}

// Method receiver edge cases - multiple scenarios
func (r MyStruct) Method1() {}
func (r *MyStruct) Method2() {}
func (r **MyStruct) Method3() {}

// Receiver with first identifier tracking
func (receiver ReceiverType) MethodName() {
	// receiver is VariableParameter
	// ReceiverType is TypeReference
}

// Function definition vs call distinction
func definedFunc() {}
func caller() {
	definedFunc()  // Call
	caller()       // Call (self-call)
}

// Type definition vs reference
type DefinedType int
func useType() {
	var x DefinedType  // Reference
	var y int
}

// State transitions after non-func/type keywords
func keywordTest() {
	import "fmt"  // Reset state
	package main  // Reset state
	var x int     // Reset state
	const c = 1   // Reset state
}

// Edge: function in ExpectingFunctionOrReceiver without (
func standalone() {}

// Edge: type after 'type' keyword
type TypeAfterType = int

// Edge: identifier that's not a function call (no parentheses)
func varTest() {
	myVar := 10
	otherVar := myVar  // Not a call
}

// Channel and map types
func typeTest() {
	var ch chan int
	var m map[string]int
	var i interface{}
}
