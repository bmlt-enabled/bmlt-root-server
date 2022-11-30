# Contributing to the BMLT Root Server

For general information about BMLT, including ways to contribute to the project, please see
[the BMLT website](https://bmlt.app).

This file contains information specifically about how to set up a development environment to work on the root server.
We want the server code (as well as code for the other project core elements) to continue to be of high quality, so
prospective developers should have a solid grounding in good software engineering practice. In other words, making
changes to the server code with the intent to contribute them back to
the main repository wouldn't be the best place to start for folks new to software development -- there are, on
the other hand, lots of other parts of the project that could very much use your time and energy! (An exception is
that we do frequently need fluent speakers of languages other than English to translate localization strings -- even
if the initial translation has already been done, there are often new strings added in subsequent development work
that need translation.)

There are various ways you can set up your development environment; in the directions here we use
[Docker](https://www.docker.com). If you don't have them already, clone the root server repo from github, and install
[Docker Desktop](https://www.docker.com/products/docker-desktop). The make file assumes docker-compose v2.

## Running the root server under docker
1. You will need to make sure you are using docker-compose v2. You can do this by opening the docker dashboard and going
to preferences then general then scroll to the bottom and check the box that says `Use Docker Compose V2`, then hit apply &
restart.
1. Copy `docker/docker-compose.dev.yml.example` to `docker/docker-compose.dev.yml` and edit it to set the `GKEY` variable to your google api key.
1. Run the command `make dev` in the top-level `bmlt-root-server` directory. If something isn't working (for example,
mising packages), try running `make clean` first and then `make dev`.
1. Browse to `http://localhost:8000/main_server/`.
1. Login with username "serveradmin" and password "CoreysGoryStory".
1. When finished, exit by pressing ctrl+c. You may also wish to delete the containers in the Docker Dashboard.


### Supported environment variables
This is an example `docker-compose.dev.yml` file. The value for each of these variables, on start of the container, is automatically
written to the appropriate line in `auto-config.inc.php`.
```
version: '3'

services:
  bmlt:
    environment:
      GKEY: ''
      DB_DATABASE: rootserver
      DB_USER: rootserver
      DB_PASSWORD: rootserver
      DB_HOST: db
      DB_PREFIX: na
      NEW_UI_ENABLED: 'true'
      AGGREGATOR_MODE_ENABLED: 'false'
```

## Developing the New UI
The new UI is developed using [React](https://reactjs.org/), and the code is located in the `resources/js` directory.

You can enable the new UI in the docker container by setting the `NEW_UI_ENABLED` environment variable to `'true'` in `docker/docker-compose.dev.yml`.

To install the UI's dependencies, run the `npm install` command from the `src` directory.

When working on the UI, you'll need to have the [Vite](https://vitejs.dev/) dev server running. To start the dev server, run `npm run dev` from the `src` directory. While the dev server is running, the UI is served out of the `resources/js` directory instead of the normal `public` directory, and [hot module replacement](https://vitejs.dev/guide/features.html#hot-module-replacement) is enabled.

## Some useful `make` commands

- `make help`  Describe all of the make commands.
- `make clean` Clean the build by removing all build artifacts and downloaded dependencies.
- `make docker` Builds the docker image. You really only need to run this when first getting set up or after a change
has been made to the Dockerfile or its base image.
- `make dev` Run the root server under docker (see above).
- `make bash` Open a bash shell on the container's file system.  This will start in the directory `/var/www/html/main_server`
- `make mysql` Start the mysql command-line client with the database `rootserver`, which holds the root server's tables.
- `make test`  Run PHP tests.

There are some additional commands as well; `make help` will list them.

## Loading a different sample database

Use this command to replace the supplied test database with your own:
```
docker exec -i docker-db-1 sh -c 'exec mysql -uroot -prootserver rootserver' < mydb.sql
```

## Running tests

Start the root server using `make dev` (see above).  Then in a separate terminal, run the tests using `make test`.
Somewhat annoyingly, `make test` will clobber your current database, so you'll need to restore it if you want to go
back to running the root server.


## Running lint
You can run the linter by running `make lint` in the top-level directory.
It doesn't work when xdebug is listening, so make sure xdebug is off first.

## Testing the install wizard
The Docker files automatically set up an `auto-config.inc.php` file for you. Usually this is great since it saves you
the bother of going through the install wizard each time you restart the root server. However, if you want to test or
change the install wizard, you can start with the install wizard instead of the login screen by deleting this file.
Here are modified steps to do that.
1. Edit `docker-compose.dev.yml` to set your google maps api key, `GKEY: API_KEY`.
1. Run the command `make dev` in the top-level `bmlt-root-server` directory.
1. In another window, run `make bash` to open a bash shell accessing the container's file system. The shell should
start in the directory `cd /var/www/html/main_server`.  
1. In the bash shell, `cd ..` to get to the parent directory, then `rm auto-config.inc.php`.
1. Leave the shell open so that you can check whether the installer generated a new `auto-config.inc.php` and if so what it contains.
1. Browse to `http://localhost:8000/main_server/`.
1. In the browser you will now be in the Install Wizard. Start by filling in the Database Connection Settings screen as follows.
```
Database Type: mysql
Database Host: db
Table Prefix: na2
Database Name: rootserver
Database User: rootserver
Database Password: rootserver
```
Note that the Database Host is `db` rather than the usual `localhost`. If you start with the install wizard, normally
you need an empty database, but the `rootserver` database already contains sample data. A convenient alternative to dropping
and (re) creating `rootserver` is to use the provided `rootserver` database, and to change the Table Prefix to `na2`, as
above.  If you need to run the installer again, just use a new Table Prefix each time (`na3` etc).

Finally, as with the earlier directions, when finished exit by pressing ctrl+c or by running `docker-compose down`.

## To debug in IntelliJ or PhpStorm (see screenshots below for more detail)

1. Open IntelliJ Preferences. Go to `Languages & Frameworks -> PHP -> Debug`. Under the `Xdebug` section, set the `Debug port` to `10000,9003`. Close IntelliJ Preferences. ![image](docker/img/intellij-prefs-xdebug.png)
1. Add a new `PHP Remote Debug` debug configuration.
1. In the new debug configuration, make click the three dots `...` next to the Server field, and add a new Server. Set the server's `Host` to `0.0.0.0`, and set the `Port` to `8000`. Check the `Use path mappings` checkbox, and set the `Absolute path on the server` for the `Project files` to `/var/www/html/main_server`.  ![image](docker/img/add-debug-server.png)
1. Check `Filter debug connection by IDE key` and set the `IDE Key(session id)` to `ROOT_SERVER_DEBUG`. ![image](docker/img/final-debug-configuration.png)
1. To start debugging, select your new debug configuration and click the `Start Listening for PHP Debug Connections` icon. ![image](docker/img/start-listening.png)
1. Then, click the `Debug` icon to open your web browser and start the XDebug session. ![image](docker/img/debug.png)
1. Then, browse to `http://0.0.0.0:8000/main_server/`
