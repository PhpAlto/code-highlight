# Line comment
=begin
Block comment
multiline
=end

# Boolean and nil
is_true = true
is_false = false
is_nil = nil

# Numbers - decimal, hex, binary, octal, float
dec = 42
hex_num = 0xFF
bin_num = 0b1010
oct_num = 0o755
flt = 3.14
exp = 1.5e10
underscore = 1_000_000

# Symbols
sym = :symbol
quote_sym = :'quoted symbol'

# Strings
str1 = "Double quoted"
str2 = 'Single quoted'
interp = "Value: #{value}"
percent = %{Percent string}
percent_q = %Q{Percent Q}
percent_q_lower = %q{Percent q}

# Heredoc
heredoc = <<HEREDOC
Multi
line
HEREDOC

# Regex
regex = /pattern/
regex_i = /pattern/i
percent_r = %r{pattern}

# Two-char operators
a == b
c != d
e <= f
g >= h
i && j
k || l
m .. n
o ... p
q => r
s += 1
t -= 1
u *= 2
v /= 2
w %= 3
x &= 4
y |= 5
z ^= 6
aa <<= 1
bb >>= 1
cc **= 2
dd <=> ee

# Single-char operators
+ - * / % = < > ! & | ^ ~ @ $ ?

# Punctuation
func(arg)
arr[0]
obj.method
{key: val}
(a, b)
:

# Keywords
def method(param)
  if condition
    puts "yes"
  elsif other
    puts "maybe"
  else
    puts "no"
  end

  unless condition
    puts "not"
  end

  case value
  when 1
    puts "one"
  when 2
    puts "two"
  else
    puts "other"
  end

  for item in items
    break if condition
    next if other
    redo if another
  end

  while condition
    retry
  end

  until condition
    puts "waiting"
  end

  begin
    raise StandardError
  rescue => e
    puts e
  ensure
    cleanup
  end

  return result
  yield block
  super

  class MyClass < Parent
    attr_reader :field
    attr_writer :other
    attr_accessor :both

    def initialize
      @instance = "var"
      @@class_var = "val"
    end

    def self.class_method
      "class"
    end

    private

    def private_method
      "private"
    end

    protected

    def protected_method
      "protected"
    end
  end

  module MyModule
    include OtherModule
    extend AnotherModule
    prepend YetAnother
  end

  alias new_name old_name
  undef method_name
  defined? variable

  lambda { |x| x + 1 }
  -> (x) { x + 1 }

  proc = Proc.new { |x| x }
end
