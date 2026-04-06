#!/bin/bash
# Edge cases for Bash coverage

# Shebang variations
#!/usr/bin/env bash
#!/bin/sh

# Comments
# Single line comment
: << 'COMMENT'
Multi-line comment block
using heredoc
COMMENT

# Variable assignments
VAR="value"
VAR='single quotes'
VAR=`command substitution`
VAR=$(command substitution)
VAR=$((arithmetic))
ARRAY=(one two three)
ASSOC_ARRAY=([key]=value)

# Variable expansions
${VAR}
${VAR:-default}
${VAR:=default}
${VAR:?error}
${VAR:+alt}
${VAR#pattern}
${VAR##pattern}
${VAR%pattern}
${VAR%%pattern}
${VAR/pattern/replace}
${VAR//pattern/replace}
${VAR^}
${VAR^^}
${VAR,}
${VAR,,}
${#VAR}
${!VAR}
$1 $2 $@ $* $# $? $$ $! $0 $_

# String handling
STRING="double quotes with $VAR"
STRING='single quotes no expansion'
STRING=$'ansi quotes\n\t'
STRING="escaped \"quotes\" and \$dollar"

# Command substitution
OUTPUT=$(ls -la)
OUTPUT=`pwd`

# Arithmetic
((i++))
((i--))
((i += 1))
result=$((10 + 20 * 3))
result=$[10 + 20]

# Conditionals
if [[ -f file ]]; then
    echo "file exists"
elif [[ -d dir ]]; then
    echo "directory exists"
else
    echo "nothing"
fi

[ -e file ] && echo "exists"
[[ $VAR == "value" ]] || echo "not equal"
test -n "$VAR" && echo "not empty"

# Case statements
case $VAR in
    pattern1) echo "match1" ;;
    pattern2|pattern3) echo "match2or3" ;;
    *) echo "default" ;;
esac

# Loops
for i in 1 2 3; do
    echo $i
done

for ((i=0; i<10; i++)); do
    echo $i
done

while read line; do
    echo "$line"
done < file

until [ $i -eq 10 ]; do
    ((i++))
done

# Functions
function func_name() {
    local var="local"
    echo "$1"
    return 0
}

func_name() {
    echo "alternative syntax"
}

# Redirections
echo "text" > file
echo "text" >> file
echo "text" 2> errors
echo "text" &> all
command < input
command 2>&1
command &> /dev/null
exec 3< file
exec 4> file
echo "text" >&3

# Pipes
command1 | command2 | command3
command1 |& command2

# Background and job control
command &
command1 & command2
wait $!

# Process substitution
diff <(ls dir1) <(ls dir2)
command >(tee file)

# Subshells and command grouping
(cd /tmp && pwd)
{ echo "group"; echo "commands"; }

# Here documents
cat << EOF
This is a heredoc
With $VAR expansion
EOF

cat << 'EOF'
This is a heredoc
Without $VAR expansion
EOF

cat <<-EOF
	Indented heredoc
	tabs removed
EOF

# Here strings
cat <<< "here string with $VAR"

# Brace expansion
echo {1..10}
echo {a..z}
echo {1..10..2}
echo file{.txt,.log,.bak}

# Globbing and pathname expansion
*.txt
**/*.sh
[abc]*
[!abc]*
?(pattern)
*(pattern)
+(pattern)
@(pattern)
!(pattern)

# Quoting and escaping
"double quotes"
'single quotes'
`backticks`
$(command)
$((math))
\$escaped

# Special parameters
$0 $1 $2 $@ $* $# $? $$ $! $- $_

# Builtin commands
echo "text"
printf "%s\n" "text"
read var
cd /path
pwd
exit 0
return 1
source file
. file
eval "command"
exec command
export VAR=value
declare -i INT=5
local var=value
readonly CONST=value
unset VAR
shift
set -- arg1 arg2
trap "command" SIGINT
ulimit -n 1024
umask 022
alias ll='ls -la'
unalias ll
type command
command -v cmd
builtin cd
enable -n cd
help command
let "i++"
mapfile -t ARRAY < file
readarray -t ARRAY < file
complete -F func cmd

# Test operators
[[ -e file ]]  # exists
[[ -f file ]]  # regular file
[[ -d dir ]]   # directory
[[ -L link ]]  # symlink
[[ -r file ]]  # readable
[[ -w file ]]  # writable
[[ -x file ]]  # executable
[[ -s file ]]  # non-empty
[[ -n str ]]   # not empty string
[[ -z str ]]   # empty string
[[ str1 == str2 ]]
[[ str1 != str2 ]]
[[ str1 < str2 ]]
[[ str1 > str2 ]]
[[ num1 -eq num2 ]]
[[ num1 -ne num2 ]]
[[ num1 -lt num2 ]]
[[ num1 -le num2 ]]
[[ num1 -gt num2 ]]
[[ num1 -ge num2 ]]
[[ expr1 && expr2 ]]
[[ expr1 || expr2 ]]
[[ ! expr ]]
[[ str =~ regex ]]

# Extended patterns
shopt -s extglob
?(pattern)
*(pattern)
+(pattern)
@(pattern)
!(pattern)

# Arrays
ARRAY[0]="first"
ARRAY[1]="second"
echo ${ARRAY[0]}
echo ${ARRAY[@]}
echo ${ARRAY[*]}
echo ${#ARRAY[@]}
echo ${!ARRAY[@]}
unset ARRAY[1]

# Associative arrays
declare -A ASSOC
ASSOC[key]="value"
echo ${ASSOC[key]}
echo ${!ASSOC[@]}

# Coprocesses
coproc COPROC { command; }
echo "input" >&${COPROC[1]}
read output <&${COPROC[0]}

# Select menu
select item in opt1 opt2 opt3; do
    echo "Selected: $item"
    break
done

# Error handling
set -e  # exit on error
set -u  # exit on undefined variable
set -o pipefail  # pipe fails if any command fails
set -x  # print commands

# Parameter expansion edge cases
${VAR:0:5}  # substring
${VAR: -5}  # last 5 chars
${!prefix*}  # indirect expansion
${!prefix@}  # indirect expansion
"${ARRAY[@]:1:2}"  # array slice

# Complex command substitution
result=$(
    multi
    line
    command
)

# Nested quotes and escaping
echo "outer \"inner\" quotes"
echo 'can'\''t escape much in single quotes'
echo $'ansi\nescapes\twork'

# Exit codes and pipelines
command1 || echo "failed"
command1 && command2 || command3
{ command1 && command2; } || command3
