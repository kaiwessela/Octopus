#! /bin/bash
if find ~/.http/blog/media -mindepth 1 | read; then
	mkdir ~/.http/temp
	cp -r ~/.http/blog/media/. ~/.http/temp
	media_found="true"
else
	media_found="false"
fi

rm -rf build
mkdir build

cp -r admin/. build/admin
cp -r api/. build/api
cp -r astronauth/. build/astronauth
cp -r Blog/. build/Blog
cp -r media/. build/media
cp -r resources/. build/resources
cp -r templates/. build/templates
cp -r vendor/. build/vendor
cp -r .htaccess build
cp -r index.php build
cp -r routes.php build

rm -rf ~/.http/blog
mkdir ~/.http/blog
cp -r build/. ~/.http/blog

if [ "$media_found" == "true" ]; then
	cp -r ~/.http/temp/. ~/.http/blog/media
	rm -rf ~/.http/temp
fi
