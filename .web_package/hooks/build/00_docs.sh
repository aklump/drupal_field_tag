#!/usr/bin/env bash
# @file Compile documentation using Knowledge

# ========= Start config =========
php_dir="$(find /Applications/MAMP/bin/php -maxdepth 1 -type d -name 'php7.4.*' | sort -V | tail -n 1)"
php="$php_dir/bin/php"
know="$HOME/Code/Packages/php/knowledge/app/bin/book.php"
# ========= End config =========

if [[ ! -x "$php" || ! -f "$know" ]]; then
  echo 'You must install https://github.com/aklump/knowledge to compile documentation' && exit 1
fi

if ! "$php" "$know" bind ./knowledge; then
  echo 'You must install https://github.com/aklump/knowledge to compile documentation' && exit 1
fi
