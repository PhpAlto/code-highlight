# Line comment
"""Triple-quoted docstring
multiline
"""
'''Another docstring'''

# Boolean and None literals
is_true = True
is_false = False
is_none = None

# Numbers - decimal, hex, binary, octal, float
dec = 42
hex_num = 0xFF
bin_num = 0b1010
oct_num = 0o755
flt = 3.14
exp = 1.5e10
neg_exp = 2e-5

# Strings
str1 = "Double quoted"
str2 = 'Single quoted'
str3 = """Triple
double"""
str4 = '''Triple
single'''
fstring = f"Value: {value}"
rawstr = r"Raw\nstring"
bytestr = b"Bytes"

# Operators - @ for decorators
@decorator
def func():
    pass

# Two-char operators
a == b
c != d
e <= f
g >= h
i and j
k or l
m // n
o ** p
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
aa //= 2
bb **= 2
cc := val

# Single-char operators
+ - * / % = < > ! & | ^ ~ @ .

# Punctuation
func(arg)
arr[0]
obj.attr
{key: val}
(a, b)
:

# Keywords and control flow
def function(param):
    if condition:
        pass
    elif other:
        pass
    else:
        pass

    for item in items:
        break
    else:
        continue

    while condition:
        pass

    try:
        raise Exception()
    except ValueError as e:
        pass
    finally:
        pass

    with open('file') as f:
        pass

    return result
    yield item

    assert condition
    import module
    from package import name
    global var
    nonlocal outer
    del variable

    class MyClass:
        pass

    async def async_func():
        await coroutine()

    lambda x: x + 1

# String prefix combinations
not_a_fstring = "x#y"  # # should not be comment
