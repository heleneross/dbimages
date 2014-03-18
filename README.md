dbimages
========

Search for current images in Joomla database - help you to find unused images

**DO NOT** use it on a live site as it is not secure and will block database connections until finished. Run it on a local copy of your site.

place a copy of the file in your Joomla images folder

## usage: ##

http://localhost/images/dbimages.php

http://localhost/images/dbimages.php?dir=employment

or whatever the path is to the images folder

if you don't add a query string then you get the files and folders in images folder, if you mistype the dir or it has no images you get no real output.

This script takes a very long time to process if you have a lot of images, can be several minutes hence `set_time_limit(0);` in file

## How it works ##
The script searches for all images in the required folder, then excludes non-image files, folders, various system files and excluded images.

It then generates an array of tables used in the db and removes excluded tables from the array.

After this it gets an array of text columns in the tables and creates the WHERE sql for the prepared statements

Using a nested foreach loop it runs through each table and column searching through the list of images 

## Notes ##

- only processes gif, png and jpg (jpeg) images
- displays a table of images found in db together with the table and count
- click on an image title to see the image in a new window
- displays a list of images not found
- displays a list of folders within the folder you are currently searching
- click on a folder name to search within that folder
- finds files in the database only and not those used in some other way such as gallery or css - use caution when deleting files!
- only searches in db text columns

## User configuration ##
You will need to edit the php file to configure. It should pick up your db details from the Joomla configuration.php file

To limit the tables to search you can add them to the $db_excluded array. 

To exclude certain images from the search add them to the $excluded_images array

If you need other types of images eg. .ico then you need them to the f_images function regex

## TODO ##

add thumbnail images to display (if it doesn't add too much to the processing time).
