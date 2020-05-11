#! /bin/bash

./build.sh

# save previously uploaded images
if [ -s /var/www/home.local/resources/images/dynamic ]
	then
		mkdir ./build/temp-images
		cp -r /var/www/home.local/resources/images/dynamic/* ./build/temp-images
		images_exist="true"
fi

# remove old files
rm -rf /var/www/home.local/*

# copy new files
cp -r ./build/. /var/www/home.local

# set permissions for image upload folder
chmod 0777 /var/www/home.local/resources/images/dynamic

# copy previously uploaded images
if [ "$images_exist" == "true" ]
	then
		cp -r /var/www/home.local/temp-images/* /var/www/home.local/resources/images/dynamic
		rm -rf /var/www/home.local/temp-images
fi
