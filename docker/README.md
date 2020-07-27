# Docker

This directory contains the Dockerfiles for building images for both the BMLT Root Server and a MySQL database with sample data for testing purposes. These images get pushed to https://hub.docker.com/r/bmltenabled/bmlt-root-server/ and https://hub.docker.com/r/bmltenabled/bmlt-root-server-sample-db/ respectively. They can be started together using docker compose.

## How to use
1. Edit `bmlt.env` to set your google maps api key, `GKEY=API_KEY`
2. Run the command `make run`
3. Browse to `http://localhost:8000/main_server/`
4. Login with username "serveradmin" and password "CoreysGoryStory"
5. When finished, exit by pressing ctrl+c or by running `docker-compose down`

## Supported environment variables
This is an example `bmlt.env` file. The value for each of these variables, on start of the container, is automatically written to the appropriate line in `auto-config.inc.php`.
```
GKEY=
DBNAME=bmlt
DBUSER=bmlt_user
DBPASSWORD=bmlt_password
DBSERVER=db
DBPREFIX=na
```

## Testing the Install Wizard
The Docker files automatically set up an `auto-config.inc.php` file for you. If you want to test the install wizard,
you can start with the install wizard instead of the login screen by deleting this file. Here
are modified steps to do that.
1. Edit `bmlt.env` to set your google maps api key, `GKEY=API_KEY`
2. Run the command `make run`
3. Run `docker exec -it docker_bmlt_1 bash` to open a bash shell accessing the container's file system. You will be in
the directory `/var/www/html/`.
4. In the bash shell run `rm auto-config.inc.php`.
5. Leave the shell open so that you can check whether the installer generated a new `auto-config.inc.php` and if so what it contains.
6. Browse to `http://localhost:8000/main_server/`
7. In the browser you will now be in the Install Wizard. Start by filling in the Database Connection Settings screen as follows.
```
Database Type: mysql
Database Host: db
Table Prefix: na2
Database Name: bmlt
Database User: bmlt_user
Database Password: bmlt_password
```
Note that the Database Host is `db` rather than the usual `localhost`. If you start with the install wizard, normally
you need an empty database, but the `bmlt` database already contains sample data. A convenient alternative to creating
a new database is to use the provided `bmlt` database, and to change the Table Prefix to `na2`, as above.  If you need
to run the installer again, just use a new Table Prefix each time (`na3` etc). If you do need to access mysql, open
another shell using `docker exec -it docker_db_1 bash`, and then run `mysql -u bmlt_user -p`.

Finally, as with the earlier directions, when finished exit by pressing ctrl+c or by running `docker-compose down`

## To debug in IntelliJ (see screenshots below for more detail)

1. Add a new configuration (ensure that you have added PHP support).
2. Select `PHP Remote Debug`
3. Add a new server, hitting the 3 dots to the right of the input box.
4. Add a server called "localhost 8000".
5. Point to hostname "localhost" and port "8000".
6. Add a path mapping for the first folder mapping to `/var/www/html`.
7. Save.
8. Set IDE key to `ROOT_SERVER_DEBUG`.
9. Save.
10. Turn on remote debugging by press the button in the toolbar. ![image1](img/3.png)
11. Set any breakpoints, and the code should pause there.

![image1](img/1.png)
![image2](img/2.png)
