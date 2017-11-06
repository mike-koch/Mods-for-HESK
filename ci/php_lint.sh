#!/bin/bash
error=false

while test $# -gt 0; do
    current=$1
    shift

    if [ ! -d $current ] && [ ! -f $current ] ; then
        echo "Invalid directory or file: $current"
        error=true

        continue
    fi

    for file in `find $current -type f -not -path "*vendor/phpunit/*" -not -path "*/api/Tests/*" -name "*.php"` ; do
        RESULTS=`php -l $file`
		echo $RESULTS

        if [ "$RESULTS" != "No syntax errors detected in $file" ] ; then
            echo $RESULTS
            error=true
        fi
    done
done


if [ "$error" = true ] ; then
    exit 1
else
    exit 0
fi


