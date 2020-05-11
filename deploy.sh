#! /bin/bash

./build.sh

# save previously uploaded images
#mv /var/www/home.local/resources/images/dynamic/* /var/www/home.local/temp-images

# remove old files
rm -rf /var/www/home.local/*

# copy new files
cp -r ./build/. /var/www/home.local

# copy previously uploaded images
#mv /var/www/home.local/temp-images/* /var/www/home.local/resources/images/dynamic 
