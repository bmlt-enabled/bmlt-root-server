# Docker

This directory contains the  Dockerfiles for building images for both the BMLT Root Server and a MySQL database with sample data for testing purposes. These images get pushed to https://hub.docker.com/r/bmltenabled/bmlt-root-server/ and https://hub.docker.com/r/bmltenabled/bmlt-root-server-sample-db/ respectively. They can be started together using docker compose.

## How to use
1. Edit bmlt.env to set your google maps api key, `GKEY=API_KEY`
2. Run the command `docker-compose up`
3. Browse to http://localhost:8000/main_server/
4. Login with username "serveradmin" and password "CoreysGoryStory"
5. When finished, exit by pressing ctrl+c or by running `docker-compose down`
