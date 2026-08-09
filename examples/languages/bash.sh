#!/usr/bin/env bash
set -euo pipefail
readonly app="alto"
files=(src/*.php)
for file in "${files[@]}"; do
  printf 'Checking %s\n' "$file"
  php -l "$file" >/dev/null
done
