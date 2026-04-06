// Line comment
/* Block comment */
/** Doc comment */

package com.example;

import java.util.*;
import static java.lang.Math.*;

// Boolean and null literals
boolean isTrue = true;
boolean isFalse = false;
Object obj = null;

// Numbers - decimal, hex, binary, octal, float
int dec = 42;
int hex = 0xFF;
int bin = 0b1010;
int oct = 0755;
float flt = 3.14f;
double dbl = 2.5;
double exp = 1.5e10;
long lng = 100L;
int underscore = 1_000_000;

// Strings and chars
String str = "Double quoted";
String escape = "Hello \"world\"";
char ch = 'A';
char escaped = '\n';

// Two-char operators
a++;
b--;
c == d;
e != f;
g <= h;
i >= j;
k && l;
m || n;
o += 1;
p -= 1;
q *= 2;
r /= 2;
s %= 3;
t &= 4;
u |= 5;
v ^= 6;
w <<= 1;
x >>= 1;
y >>>= 1;
z -> lambda;

// Single-char operators
+ - * / % = < > ! & | ^ ~ ?

// Punctuation
func(arg);
arr[0];
obj.field;
{block}
(a, b);
:

// Annotations
@Override
@SuppressWarnings("unchecked")
@Deprecated
public class MyClass extends Parent implements Interface {

    // Keywords
    public static final int CONST = 1;
    private int field;
    protected String name;
    volatile int vol;
    transient Object trans;

    public MyClass() {
        super();
        this.field = 0;
    }

    public void method() {
        if (condition) {
            return;
        } else if (other) {
            System.out.println("test");
        } else {
            throw new Exception();
        }

        for (int i = 0; i < 10; i++) {
            break;
        }

        for (String item : items) {
            continue;
        }

        while (condition) {
            do {
                something();
            } while (another);
        }

        switch (value) {
            case 1:
                break;
            case 2:
            case 3:
                break;
            default:
                break;
        }

        try {
            risky();
        } catch (IOException e) {
            handle();
        } catch (Exception ex) {
            handleOther();
        } finally {
            cleanup();
        }

        synchronized (lock) {
            criticalSection();
        }

        assert condition : "message";

        var lambda = (x, y) -> x + y;
        Runnable r = () -> System.out.println("hi");
    }

    abstract class AbstractClass {
        abstract void abstractMethod();
    }

    interface MyInterface {
        void interfaceMethod();
    }

    enum MyEnum {
        VALUE1, VALUE2, VALUE3
    }

    @interface MyAnnotation {
        String value();
    }

    record Point(int x, int y) {}

    sealed class SealedClass permits SubClass {}
    final class SubClass extends SealedClass {}

    native void nativeMethod();
    strictfp double strictMethod() { return 0.0; }

    // Generics
    List<String> list = new ArrayList<>();
    Map<String, Integer> map = new HashMap<>();

    // instanceof and cast
    if (obj instanceof String) {
        String s = (String) obj;
    }

    // new keyword
    Object newObj = new Object();
    int[] array = new int[10];
    int[][] matrix = new int[5][5];

    // goto and const (reserved but unused)
    // goto label;
    // const int VAL = 1;
}
