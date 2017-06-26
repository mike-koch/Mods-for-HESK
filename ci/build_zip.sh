#!/bin/bash

cd ../
mkdir $VERSION_NUMBER
TARGETDIR=$VERSION_NUMBER;for file in *;do test "$file" != "$TARGETDIR" && cp -r "$file" "$TARGETDIR/";done

# Remove files that we don't want to bundle
cd $VERSION_NUMBER
rm -rf ci
#rm .gitignore
#rm .gitlab-ci.yml
rm -rf .git
rm apidoc.json
rm CONTRIBUTING.md

cd ../
zip -r release.zip $VERSION_NUMBER
rm -rf $VERSION_NUMBER