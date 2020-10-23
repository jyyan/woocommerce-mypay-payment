#/bin/sh
DATE=`date '+%Y%m%d_%H%M'`
ZIP_NAME=woocomerce-mypay-payment

cp -r src $ZIP_NAME 
zip -r $(printf "%s_%s.zip" "$ZIP_NAME" "$DATE") $ZIP_NAME
rm -Rf $ZIP_NAME

mv $(printf "%s_%s.zip" "$ZIP_NAME" "$DATE") ./release/.

echo $DATE