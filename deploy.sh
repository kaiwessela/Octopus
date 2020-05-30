#! /bin/bash

./build.sh

# save previously uploaded images
if find /var/www/home.local/resources/images/dynamic -mindepth 1 | read; then
	images_exist="false"
else
	mkdir ./build/temp-images
    cp -r /var/www/home.local/resources/images/dynamic/* ./build/temp-images
    images_exist="true"
fi

# remove old files
rm -rf /var/www/home.local/*

# copy new files
cp -r ./build/. /var/www/home.local

# copy previously uploaded images
if [ "$images_exist" == "true" ]; then
	cp -r /var/www/home.local/temp-images/* /var/www/home.local/resources/images/dynamic
	rm -rf /var/www/home.local/temp-images
fi

# set permissions for image upload folder
chown -R www-data /var/www/home.local/resources/images/dynamic
