[![Build Status](https://travis-ci.org/PW-Solutions/miquiz-moodle.svg?branch=master)](https://travis-ci.org/PW-Solutions/miquiz-moodle)

# MI-Quiz-Moodle-Plugin
Activity module for Moodle that enables you to use already existing Moodle
structures like questions and courses to create quizzes in MI-Quiz.

## Install plugin in Moodle
TODO

Make sure to enable web services and the external service `moodle_mobile_app`,
so that MI-Quiz can authenticate users with Moodle credentials. See:
`/admin/webservice/service.php?id=1`, `/admin/search.php?query=enablewebservices`, and `/admin/settings.php?section=mobilesettings`.

## Development

This repository comes with a ready to use docker configuration to test the plugin (without the connection to MI Quiz).
1. Clone repository
2. Create `.env` file: `cp .env.example .env`
3. Update `SERVER` variable in `.env` file
4. Start containers with `docker-compose up -d`
5. Login under `SERVER` and use default admin account: `admin`: `Admin123.`

**Important:** To perform the initial moodle installation with this plugin already imported, we
have to temporary comment out the lines in `lang\{en|de}\miquiz.php` where `get_config` is called,
as Moodle tries to get the config value from the database before any tables are created.

### Available users
- admin:Admin123. = Admin
- alice:Alice123. = User
- bob:Bob1234. = Teacher

### Reset DB
`docker-compose down -v`

### Recreate moodle image
`docker-compose build --no-cache`

### Create new startup_db
`docker exec -it moodle_db_1 mysqldump -u moodle -pmoodle moodle -r startup_db.sql`
`docker cp moodle_db_1:startup_db.sql ./`

See https://stackoverflow.com/a/20086949/5816097 on why we cannot use `... > startup_db.sql`.

### Cron
There is no cron-job configured in the docker image (yet). To perform the Moodle cron job, execute
`docker exec -it moodle_moodle_1 php admin/cli/cron.php`. This is necessary to sync users
of activities with miquiz.

### Connect with MI-Quiz development workspace

Default host: http://host.docker.internal:8000 (Moodle plugin settings)

If you use another port for MI-Quiz in your docker port, change it in the moodle plugin settings.
*Important*: If you use Docker on Linux, you need to add the host ip in the `/etc/hosts` file yourself (see
[open issue](https://github.com/docker/for-linux/issues/264)).