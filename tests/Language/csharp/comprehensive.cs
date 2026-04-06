/// Doc comment
// Line comment
/* Block comment */
/** Doc block comment */

#region Directives
using System;
#endregion

namespace MyNamespace
{
    // Boolean and null literals
    var isTrue = true;
    var isFalse = false;
    var isNull = null;

    // Numbers - decimal, hex, binary, float
    var dec = 42;
    var hex = 0xFF;
    var bin = 0b1010;
    var flt = 3.14f;
    var dbl = 2.5;
    var exp = 1.5e10;
    var suffix = 100L;

    // Strings
    var str = "Hello \"world\"";
    var verbatim = @"C:\Path\To\File";
    var interpolated = $"Value: {value}";
    var verbatimInterp = @$"Path: {path}";
    var altInterp = $@"Alt: {alt}";

    // Char literal
    var ch = 'A';
    var escaped = '\n';

    // Two-char operators
    a++;
    b--;
    c == d;
    e != f;
    g <= h;
    i >= j;
    k && l;
    m || n;
    o ?? p;
    q?.Prop;
    r => result;
    s -> target;
    T::Member;
    u += 1;
    v -= 1;
    w *= 2;
    x /= 2;
    y %= 3;
    z &= 4;
    aa |= 5;
    bb ^= 6;
    cc <<= 1;
    dd >>= 1;
    ee ??= val;
    ff << 1;
    gg >> 1;

    // Single-char operators
    + - * / % = < > ! & | ^ ~ ? .

    // Punctuation
    func(arg);
    arr[0];
    obj.field;
    { block }

    // Attributes
    [Attribute]
    [Serializable]
    public class MyClass
    {
        // Keywords
        public static async Task Main()
        {
            if (condition) {}
            else {}

            for (int i = 0; i < 10; i++) {}
            foreach (var item in items) {}
            while (condition) {}
            do {} while (condition);

            switch (value)
            {
                case 1:
                    break;
                default:
                    continue;
            }

            try {
                throw new Exception();
            }
            catch (Exception ex) {
            }
            finally {
            }

            var lambda = (x, y) => x + y;
            var query = from x in items
                       where x > 0
                       select x;

            lock (obj) {}
            using (var resource = new Resource()) {}

            return result;
            yield return item;
            await Task.Delay(100);

            checked { var sum = a + b; }
            unchecked { var diff = a - b; }
            unsafe { int* ptr = null; }
            fixed (byte* p = bytes) {}

            var size = sizeof(int);
            var allocated = stackalloc int[10];
            var type = typeof(string);
            var name = nameof(variable);

            goto Label;
            Label:

            class LocalClass {}
            interface IInterface {}
            struct MyStruct {}
            enum MyEnum {}
            delegate void MyDelegate();
            event EventHandler MyEvent;

            abstract class AbstractClass {}
            sealed class SealedClass {}
            partial class PartialClass {}
            static class StaticClass {}

            public int Public;
            private int Private;
            protected int Protected;
            internal int Internal;
            readonly int ReadOnly;
            const int Const = 1;
            volatile int Volatile;

            virtual void Virtual() {}
            override void Override() {}
            explicit operator int(MyClass m) => 0;
            implicit operator string(MyClass m) => "";
            operator +(MyClass a, MyClass b) => a;

            ref int Ref(ref int x) => ref x;
            out int Out(out int x) { x = 0; return x; }
            params int[] Params(params int[] args) => args;

            var record = new Record { Prop = "value" } with { Prop = "new" };

            var query2 = from x in items
                        where x.Value > 0
                        group x by x.Type into g
                        orderby g.Key
                        select g;

            global::System.Console.WriteLine();
        }
    }
}
