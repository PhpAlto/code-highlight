// More edge cases for Java coverage

// Text blocks (Java 13+)
String textBlock = """
    Text block
    with multiple lines
    """;

// Switch expressions (Java 14+)
int result = switch (value) {
    case 1, 2 -> 1;
    case 3 -> 2;
    default -> {
        System.out.println("default");
        yield 0;
    }
};

// Pattern matching instanceof (Java 16+)
if (obj instanceof String s) {
    System.out.println(s.length());
}

// Records (Java 16+)
record Point(int x, int y) {}
record Person(String name, int age) implements Serializable {}

// Sealed classes (Java 17+)
sealed class Shape permits Circle, Square {}
final class Circle extends Shape {}
non-sealed class Square extends Shape {}

// Complex annotations
@SuppressWarnings({"unchecked", "deprecation"})
@Deprecated(since = "1.5", forRemoval = true)
@Target({ElementType.METHOD, ElementType.TYPE})
@Retention(RetentionPolicy.RUNTIME)
@interface CustomAnnotation {
    String value() default "";
    int count() default 0;
}

// Nested annotations
@interface Outer {
    @interface Inner {
        String value();
    }
}

// Varargs
void method(String... args) {}
void method(int first, String... rest) {}

// Generic method with bounds
<T extends Comparable<T> & Serializable> void genericMethod(T value) {}

// Wildcard generics
List<? extends Number> upperBound;
List<? super Integer> lowerBound;
List<?> unbounded;
Map<?, ?> mapWildcard;

// Method references
Consumer<String> printer = System.out::println;
Supplier<List> listFactory = ArrayList::new;
Function<String, Integer> parser = Integer::parseInt;
BiFunction<Integer, Integer, Integer> adder = Integer::sum;

// Complex lambda expressions
BiFunction<Integer, Integer, Integer> complex = (a, b) -> {
    int result = a + b;
    return result * 2;
};

// Try-with-resources multiple
try (FileReader fr = new FileReader("file");
     BufferedReader br = new BufferedReader(fr)) {
    return br.readLine();
}

// Enhanced switch with null
String result = switch (value) {
    case null -> "null value";
    case "a" -> "letter a";
    default -> "other";
};

// Numeric literals with underscores edge cases
int bin = 0b1010_1010_1010_1010;
int hex = 0xDEAD_BEEF;
long large = 1_000_000_000_000L;
double decimal = 3.141_592_653_589_793;

// Character escapes
char tab = '\t';
char newline = '\n';
char carriageReturn = '\r';
char backspace = '\b';
char formFeed = '\f';
char singleQuote = '\'';
char doubleQuote = '\"';
char backslash = '\\';
char unicode = '\u03A9';
char octal = '\101';

// String escape sequences
String escapes = "tab:\t newline:\n return:\r backspace:\b formfeed:\f quote:\" slash:\\ unicode:\u03A9";

// Multi-catch exception
try {
    riskyOperation();
} catch (IOException | SQLException ex) {
    handle(ex);
}

// Diamond operator with anonymous class
Map<String, List<String>> map = new HashMap<>() {{
    put("key", new ArrayList<>());
}};

// Local variable type inference (var)
var number = 42;
var text = "string";
var list = new ArrayList<String>();
var lambda = (Runnable) () -> {};

// Complex array initializations
int[][] matrix = {{1, 2}, {3, 4}};
int[] array = new int[]{1, 2, 3};
String[] strings = {"a", "b", "c"};

// Anonymous classes
Runnable r = new Runnable() {
    @Override
    public void run() {
        System.out.println("running");
    }
};

// Interface with default and static methods
interface MyInterface {
    void abstractMethod();

    default void defaultMethod() {
        System.out.println("default");
    }

    static void staticMethod() {
        System.out.println("static");
    }

    private void privateMethod() {
        System.out.println("private");
    }
}

// Enum with methods and constructors
enum Day {
    MONDAY("Mon"),
    TUESDAY("Tue"),
    WEDNESDAY("Wed");

    private final String abbr;

    Day(String abbr) {
        this.abbr = abbr;
    }

    public String getAbbreviation() {
        return abbr;
    }
}

// Static imports
import static java.lang.Math.PI;
import static java.lang.Math.sqrt;
import static java.util.Collections.*;

// Package private class
class PackagePrivate {}

// Abstract class with abstract and concrete methods
abstract class AbstractBase {
    abstract void abstractMethod();

    void concreteMethod() {
        System.out.println("concrete");
    }
}

// Final class that can't be extended
final class FinalClass {}

// Static nested class
class Outer {
    static class StaticNested {}

    class InnerNonStatic {}
}

// Local class
void method() {
    class Local {
        void localMethod() {}
    }
}

// Assert statement
assert condition : "message";
assert value > 0;

// Labeled statements
outer: for (int i = 0; i < 10; i++) {
    inner: for (int j = 0; j < 10; j++) {
        if (condition) break outer;
        if (other) continue inner;
    }
}

// Synchronized block
synchronized (lock) {
    criticalSection();
}

// Synchronized method
synchronized void synchronizedMethod() {}

// Volatile and transient
volatile int volatileField;
transient int transientField;

// Native method
native void nativeMethod();

// Strictfp
strictfp class StrictFPClass {}

// Module declarations (Java 9+)
// (normally in module-info.java)
// module com.example.mymodule {
//     requires java.base;
//     exports com.example.api;
//     opens com.example.internal;
// }
