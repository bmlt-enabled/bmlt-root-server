# Setting Up a BMLT Root Server

As of version 4.0.0 of the root server, we are dropping support for the Installer Wizard. Setting up a completely new root server
is at this point an infrequent event, and hopefully these directions will be clear enough.

For a more detailed but older tutorial please see https://bmlt.app/setting-up-the-bmlt/. That tutorial is based on the Installer Wizard, so you'll need to adapt it accordingly. But it may be useful to explain some of the steps. Differences are noted below.

## Setting Up an Empty MySQL Database

Set up an empty MySQL database, along with a MySQL user that has access to it.  (The directions for this step in the older tutorial are still valid.) The standard name for this database is `rootserver`, but you can name it something else if you prefer.

## Uploading the BMLT Server Zip File

Get the latest version of the server from https://github.com/bmlt-enabled/bmlt-root-server/releases, and upload it to your web hosting provider's server. (The directions for this step in the older tutorial are also still valid.) For this part of the step, upload the zip file *without* unzipping it on your local machine. Then unzip it on your server. You should end up with a directory `main_server` under the directory that holds the files that show up on your website. Thus, if your web hosting server has a directory `public_html` for the files that show up on your website, put `main_server` in that directory, like this: `public_html/main_server`.

In addition, unzip the file on your laptop or desktop machine -- you'll need to get a couple of files from it in the following steps. But don't try to upload the unzipped file -- that can result in problems with dropped files and such.

## Initializing the MySQL Database

This step is different from the old tutorial.

In the unzipped version of the BMLT Server on your local machine, locate the directory `installer` and find the file `initial-database.sql` in that directory.  Import the contents of this file into the empty MySQL database that you set up in the first step.  (If you are using cPanel, find the `phpMyAdmin` tool under `Databases`, select your new database, and then click `Import`.)

## Adding the auto-config File

This step is also different from the old tutorial.

In the unzipped version of the BMLT Server, look again in the directory `installer` and find the file `auto-config.inc.php`. Upload this file to your server and put it in the directory that holds your `main_server` directory.  Verify that this file still has the permissions `-rw-r--r--` (`0644` in octal). This means that the owner of the file can read and write it, and the owning group and others can read it.

Note that the file `auto-config.inc.php` is not inside `main_server`, but rather at the same level. This is a little weird, but does have the advantage that you can upload a new version of the server easily without touching the `auto-config.inc.php` file.  So your directory structure should look something like this:
```
public_html
   auto-config.inc.php
   main_server
      app
      bootstrap
      ......
```

Now edit the `auto-config.inc.php` file with new parameters as needed. You can do this using the `edit` command on cPanel. There are two parameters you definitely need to update, namely `$dbUser` and `$dbPassword` (the user and password for your root server database). You also need to either update the parameter `$gkey` if you are using Google Maps, or else delete this parameter altogether if you are using OSM (Open Street Maps) for maps and nominatim for geocoding.

There are various other parameters in the file, but the default values may well be what you want.

There are a couple of alternative ways to add the auto-config file. Rather than the above, you can locate the file in the `main_server/installation` directory on the server and copy it from there.  Or you could edit the `auto-config.inc.php` file on your local machine, and then upload the edited file, thus avoiding needing to edit it on your web host. If you edit it on your local machine, be sure and use an editor intended for editing source code and not something like Microsoft Word.

## Initial Log In

Now you should be able to go to the website for your new server (which might be at a URL like `https://bmlt.myregion.org/main_server/`). Log in as user `serveradmin` password `change-this-password-first-thing`. As the initial password suggests (not very subtly), first go to the `Account` tab and change the password to something unique for your BMLT root server.

## Adding Users and Service Bodies

At this point you can set up one or more Service Body Administrators and Service Bodies, and start adding meetings. We are now back to steps that are unchanged from the old tutorial, so refer to that for details.
