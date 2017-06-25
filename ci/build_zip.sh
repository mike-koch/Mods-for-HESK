#!/bin/bash

cd ../
mkdir release-temp
TARGETDIR='release-temp';for file in *;do test "$file" != "$TARGETDIR" && cp -r "$file" "$TARGETDIR/";done

# Remove files that we don't want to bundle
cd release-temp
rm -rf ci
#rm .gitignore
#rm .gitlab-ci.yml
rm -rf .git
rm apidoc.json
rm CONTRIBUTING.md

cd ../
zip -r release.zip release-temp
rm -rf release-temp