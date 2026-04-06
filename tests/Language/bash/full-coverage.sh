#!/bin/bash

# Escaped quotes in double-quoted strings
echo "She said \"hello\" to me"
echo "Path: \"C:\\Program Files\\test\""

# Backslash escaping in double-quoted strings
echo "Line 1\nLine 2"
echo "Tab\there"
echo "Escaped backslash: \\"

# Here-doc with dash (<<-)
cat <<-EOF
	This is indented
	And will be dedented
	EOF

# Braced variables
echo "${HOME}"
echo "${USER:-default}"
echo "${VAR%suffix}"
echo "${VAR#prefix}"

# Special variables
echo "Script name: $0"
echo "Argument count: $#"
echo "All arguments: $@"
echo "Exit status: $?"
echo "Current flags: $-"
echo "Process ID: $$"
echo "Background PID: $!"

# Escaped backticks
result=`echo \`nested\` command`

# Nested parentheses in command substitution
output=$(echo $(echo $(echo nested)))
calc=$((1 + (2 * (3 + 4))))