# More edge cases for Ruby coverage

# String interpolation edge cases
"simple #{var} interpolation"
"nested #{outer "#{inner}"} interpolation"
"multiple #{a} and #{b} #{c}"
"complex #{obj.method(arg)}"

# Regular expressions
/simple regex/
/regex with #{interpolation}/
/[character class]/
/escape \/ slash/
/^anchors$/
/(groups)/
/(?:non-capturing)/
/\d+ \w+ \s+/
%r{alternative regex}
%r|pipe delimiters|
%r!exclamation!

# Symbols
:simple
:'single quoted'
:"double #{quoted}"
:"escape \" quote"

# Percent literals
%w[word array]
%W[word array with #{interpolation}]
%i[symbol array]
%I[symbol array with #{interpolation}]
%q{single quoted}
%Q{double quoted with #{interpolation}}
%x{command execution}
%s{symbol}
%r{regex}

# Heredocs
<<EOF
Basic heredoc
EOF

<<-EOF
  Indented heredoc
EOF

<<~EOF
  Squiggly heredoc
  removes leading whitespace
EOF

<<"EOF"
Double quoted heredoc with #{interpolation}
EOF

<<'EOF'
Single quoted heredoc
no #{interpolation}
EOF

<<`EOF`
Command heredoc
EOF

# Numbers with underscores
1_000_000
3.14_15_92
0x_FF_FF
0b_1010_1010
0o_755_755

# Binary, octal, hex numbers
0b101010
0o755
0xFFFF
0xabcdef
0XABCDEF

# Float with exponent
1.5e10
2.3e-5
4.5E+3

# Character literals
?a
?\\
?\n
?\t
?\s

# Range operators
1..10
1...10
'a'..'z'
start..end

# Special variables
$0 $1 $2 $& $` $' $+ $~
$! $@ $/ $\ $; $, $. $=
$* $$ $? $: $"
$LOAD_PATH
$LOADED_FEATURES
$DEBUG
$VERBOSE

# Global variables
$global_var
$_special
$-w
$-0

# Instance variables
@instance
@_private
@@class_variable

# Constants
CONST
Module::CONST
::TopLevel::CONST

# Class and module definitions
class SimpleClass
end

class InheritedClass < ParentClass
end

class << self
  def singleton_method
  end
end

module SimpleModule
end

module Nested::Module
end

# Method definitions with various syntaxes
def simple_method
end

def method_with_args(a, b, c)
end

def method_with_default(a = 1, b = 2)
end

def method_with_splat(*args)
end

def method_with_keyword(key: 'value')
end

def method_with_double_splat(**kwargs)
end

def method_with_block(&block)
end

def method_with_all(a, b = 2, *args, key: 'val', **kwargs, &block)
end

def method?
  true
end

def method!
  mutate!
end

def method=(value)
  @value = value
end

def []=(index, value)
  @arr[index] = value
end

def [](index)
  @arr[index]
end

def +(other)
  self.value + other.value
end

def self.class_method
end

# Lambda and proc
lambda { |x| x * 2 }
->(x) { x * 2 }
proc { |x| x * 2 }
Proc.new { |x| x * 2 }

# Blocks
[1, 2, 3].each { |x| puts x }
[1, 2, 3].each do |x|
  puts x
end

# Case statements
case value
when 1
  'one'
when 2, 3
  'two or three'
when 4..10
  'four to ten'
when String
  'string'
when /regex/
  'matches regex'
else
  'default'
end

# Rescue and exception handling
begin
  risky_operation
rescue StandardError => e
  handle_error(e)
rescue AnotherError, YetAnother => e
  handle_multiple(e)
rescue
  handle_any
ensure
  cleanup
end

def method_with_rescue
  operation
rescue
  fallback
end

value rescue default

# Modifiers
puts "hello" if condition
puts "hello" unless condition
puts "hello" while condition
puts "hello" until condition
for i in 1..10 do
  puts i
end

# Ternary operator
condition ? true_value : false_value

# Safe navigation
object&.method&.chain

# Double colon
Module::Constant
object::method

# Embedded documents
=begin
Multi-line comment
spanning multiple lines
=end

__END__
Everything after this is ignored
can put documentation here

# Method visibility
class VisibilityExample
  public
  def public_method
  end

  protected
  def protected_method
  end

  private
  def private_method
  end
end

# Alias
alias new_name old_name
alias :symbol_new :symbol_old

# Defined?
defined? variable
defined?(method)

# BEGIN and END
BEGIN {
  puts "startup"
}

END {
  puts "cleanup"
}

# Special methods
attr_reader :name
attr_writer :age
attr_accessor :email
attr :old_style

# Module include/extend/prepend
include ModuleName
extend ModuleName
prepend ModuleName

# Refinements
using MyRefinement

# Method_missing
def method_missing(method, *args, &block)
  super
end

# Respond_to_missing
def respond_to_missing?(method, include_private = false)
  super
end

# Autoload
autoload :ConstantName, 'file/path'

# Complex regex with modifiers
/regex/i
/regex/m
/regex/x
/regex/imx

# Numeric methods
42.times { }
10.upto(20) { }
20.downto(10) { }
5.step(50, 5) { }

# String methods
"string".upcase
"string".downcase
"string".capitalize
"string".reverse
"string".length
"string".size
"string".empty?
"string".include?("sub")
"string".start_with?("str")
"string".end_with?("ing")

# Array methods
[].push(1)
[].pop
[].shift
[].unshift(1)
[].first
[].last
[].size
[].length
[].empty?
[].include?(1)

# Hash methods
{}.keys
{}.values
{}.has_key?(:key)
{}.has_value?(value)
{}.merge(other)
{}.fetch(:key, default)

# Parallel assignment
a, b = 1, 2
a, *rest = [1, 2, 3, 4]
*start, last = [1, 2, 3, 4]
first, *middle, last = [1, 2, 3, 4]

# Conditional assignment
a ||= default
a &&= new_value

# Pattern matching (Ruby 2.7+)
case value
in pattern
  matched
in [a, b]
  array_pattern
in {key: value}
  hash_pattern
else
  no_match
end

# Endless method def (Ruby 3.0+)
def endless_method = expression

# Numbered parameters
[1, 2, 3].map { _1 * 2 }
[[1, 2], [3, 4]].map { _1 + _2 }

# Rightward assignment (Ruby 3.1+)
expression => variable

# Multiple return values
return a, b, c

# Yield with block_given?
def method_with_yield
  yield if block_given?
end

# Super variations
super
super()
super(arg1, arg2)

# Retry in rescue
begin
  attempt
rescue
  retry
end
