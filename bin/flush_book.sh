#!/usr/bin/env bash

source="${BASH_SOURCE[0]}"
source="${BASH_SOURCE[0]}"
if [[ ! "$source" ]]; then
  source="${(%):-%N}"
fi
while [ -h "$source" ]; do # resolve $source until the file is no longer a symlink
  dir="$( cd -P "$( dirname "$source" )" && pwd )"
  source="$(readlink "$source")"
  [[ $source != /* ]] && source="$dir/$source" # if $source was a relative symlink, we need to resolve it relative to the path where the symlink file was located
done
app_root="$( cd -P "$( dirname "$source" )/../" && pwd )"

cd "$app_root" && php ./knowledge/vendor/aklump/knowledge/bin/handle_page_change.php './knowledge/pages/**/*'
