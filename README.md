# MI-Quiz-Moodle-Plugin
Activity module for Moodle that enables you to use already existing Moodle
structures like questions and courses to create quizzes in MI-Quiz.

## Install plugin in Moodle
TODO

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

### Connect with MI-Quiz development workspace

Default host: dev.mi-quiz (Moodle plugin settings)
Add the host in `/etc/hosts`


# TODOs
- clean up code
