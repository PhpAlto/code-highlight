#!/bin/bash
# Comprehensive coverage for BashLanguage - targeting all uncovered lines

# Comments at start of line
# Comment after space
echo "test" # inline comment

# Double-quoted strings with all edge cases
"simple string"
"string with $VAR"
"string with ${VAR}"
"string with $(command)"
"string with $((1+2))"
"string with \" escaped quotes"
"string with \\ backslash"
"multi
line
string"
""

# Single-quoted strings
'simple'
'with $VAR no expansion'
'can'\''t'
''

# Heredoc variants - CRITICAL for coverage
cat <<EOF
basic heredoc
EOF

cat <<-EOF
	indented heredoc with dash
EOF

cat << EOF
heredoc with space before delimiter
EOF

cat <<DELIMITER
custom delimiter name
DELIMITER

cat <<"EOF"
quoted delimiter
EOF

cat <<'EOF'
single quoted delimiter
EOF

cat <<`EOF`
backtick delimiter
EOF

# Heredoc with whitespace before delimiter name
cat <<   SPACED
content
SPACED

# Variables - all forms
$var
${var}
${var:-default}
${var:=default}
${var:?error}
${var:+alternate}
${var#pattern}
${var##pattern}
${var%pattern}
${var%%pattern}
${var/pattern/replacement}
${var//pattern/replacement}
${!var}
$1 $2 $@ $* $# $? $$ $! $_

# Arithmetic expansion
$((1 + 2))
$(( 10 * 20 ))
$((x++))
$((y--))

# Command substitution
$(ls)
$(echo "nested")
`backtick command`
`echo test`

# Keywords and builtins
if true; then
    echo "if"
elif false; then
    echo "elif"
else
    echo "else"
fi

for i in 1 2 3; do
    echo $i
done

while true; do
    break
done

case $var in
    pattern1)
        echo "1"
        ;;
    pattern2)
        echo "2"
        ;;
esac

function func_name {
    echo "function"
}

export VAR=value
local local_var=value
declare -i int_var=5
readonly CONST=value
typeset var
unset variable
alias ll='ls -la'
unalias ll

echo "builtin echo"
printf "%s\n" "printf"
read input
cd /path
pwd
ls
rm file
cp src dst
mv old new
mkdir dir
rmdir dir
touch file
cat file
grep pattern file
sed 's/old/new/' file
awk '{print}' file
test -f file
true
false
exit 0
return 1
set -e
shopt -s nullglob

# Whitespace edge cases
   echo "leading spaces"
	echo "leading tab"
echo    "multiple    spaces"

# Empty lines and newlines


# Complex combinations
"string with $(command `nested`) and ${var}"
$(echo "command with $VAR and `backtick`")
$((1 + $(echo 2) + $var))

# Special characters
echo $'special\nchars\twith\rescapes'
echo "pipe | test"
echo "redirect > test"
echo "ampersand & test"

# Variable at different positions
VAR="start"
echo $VAR "middle" $VAR
$VAR="end"
