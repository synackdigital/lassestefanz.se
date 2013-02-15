#!/usr/bin/env bash

# Iterate files to replace some of our keys
echo "Minifying javascript"
for file in js/*.js
do  
    if [[ $file != *.min.js ]] ; then
        echo "Minifying" $file
        
        base=${file%.js}
        min_name="${base}.min.js"
        
        yuicompressor "$file" > "$min_name"
    fi
done

echo "Creating PHP Documentation"
mkdir -p docs
PACKAGE=$(basename `pwd`)
phpdoc -t docs -d . -dn $PACKAGE

exit 0
