#!/bin/bash

cd ../
mkdir release
TARGETDIR=release;for file in *;do test "$file" != "$TARGETDIR" && cp -r "$file" "$TARGETDIR/";done

# Remove files that we don't want to bundle
cd release
rm -rf ci
rm -rf .git
rm apidoc.json
rm CONTRIBUTING.md

cd ../