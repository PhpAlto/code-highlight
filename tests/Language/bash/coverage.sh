# Comment at start
echo "Double quoted \"string\" with backslash \\"
echo 'Single quoted string'
cat <<EOF
Here doc content
EOF
cat <<-INDENTED
	Indented here doc
INDENTED
var=$(( 1 + 2 * (3 - 1) ))
result=$(date +%Y)
legacy=`ls -la`
echo ${complex_var} $simple_var $? $# $@
if [ -f file ]; then
  exit 0
fi
