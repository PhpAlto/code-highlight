// Line comment
/* Block comment */
/// Doc comment
/** Doc block */

// Boolean and nil
let isTrue: Bool = true
let isFalse: Bool = false
let isNil: String? = nil

// Numbers - decimal, hex, binary, octal, float
let dec = 42
let hex = 0xFF
let bin = 0b1010
let oct = 0o755
let flt = 3.14
let exp = 1.5e10
let underscore = 1_000_000

// Strings
let str1 = "Double quoted"
let interp = "Value: \(value)"
let multi = """
Multi
line
"""

// Two-char operators
a == b
c != d
e <= f
g >= h
i && j
k || l
m ... n
o ..< p
q += 1
r -= 1
s *= 2
t /= 2
u %= 3
v &= 4
w |= 5
x ^= 6
y <<= 1
z >>= 1
aa -> bb
cc ?? dd
ee ?. ff
gg => hh

// Single-char operators
+ - * / % = < > ! & | ^ ~ @ . ?

// Punctuation
func(arg)
arr[0]
obj.property
{block}
(a, b)
:

// Keywords
func function(param: Int) -> Int {
    if condition {
        return result
    } else if other {
        return 0
    } else {
        return 1
    }

    guard let value = optional else {
        return 0
    }

    switch value {
    case 0:
        break
    case 1...9:
        fallthrough
    default:
        continue
    }

    for item in items {
        break
        continue
    }

    while condition {
        repeat {
            break
        } while another
    }

    repeat {
        if done { break }
    } while condition

    struct MyStruct {
        var field: Int
        let constant: String
    }

    class MyClass: ParentClass {
        init() {
            super.init()
        }

        deinit {
            cleanup()
        }

        func method() {
            self.field = 0
        }

        static func classMethod() {}
        class func overridableClassMethod() {}

        private func privateMethod() {}
        fileprivate func filePrivateMethod() {}
        internal func internalMethod() {}
        public func publicMethod() {}
        open func openMethod() {}
    }

    enum MyEnum {
        case variant1
        case variant2(Int)
        case variant3(x: Int)
    }

    protocol MyProtocol {
        func method()
        var property: Int { get set }
    }

    extension MyClass: MyProtocol {
        func method() {}
    }

    typealias Alias = [Int]

    let closure = { (x: Int) -> Int in
        return x + 1
    }

    let shortClosure = { $0 + 1 }

    var computed: Int {
        get { return value }
        set { value = newValue }
    }

    lazy var lazyProp = expensive()
    weak var weakRef: AnyObject?
    unowned var unownedRef: AnyObject

    @available(iOS 14.0, *)
    @objc func objcMethod() {}

    #if DEBUG
    print("debug")
    #elseif RELEASE
    print("release")
    #else
    print("other")
    #endif

    as! Type
    as? Type
    as Type
    is Type

    try expression
    try? expression
    try! expression

    throw error
    throws
    rethrows

    async func asyncFunc() async throws {
        await something()
    }

    actor MyActor {
        func method() {}
    }

    import Foundation
    import struct Module.Type

    inout parameter
    mutating func mutate() {}
    nonmutating func noMutate() {}

    subscript(index: Int) -> Int {
        get { return array[index] }
        set { array[index] = newValue }
    }

    operator +++ : AdditionPrecedence
    precedencegroup CustomPrecedence {}

    where T: Protocol
    associatedtype Associated

    required init() {}
    convenience init() {}
    indirect enum Tree {}

    dynamic func dynamicMethod() {}
    final class FinalClass {}
    optional func optionalMethod() {}

    left associativity
    right associativity
    none associativity
}
