FROM php:7.1-apache

ARG MOODLE_URL

RUN apt-get update &&\
apt-get install -y nano zlib1g-dev libpng-dev libicu-dev libxml2-dev &&\
docker-php-ext-install mysqli zip gd intl xmlrpc soap &&\
mkdir /var/www/moodledata &&\
chown -R www-data:www-data /var/www/moodledata &&\
cd /var/www/html &&\
curl https://download.moodle.org/download.php/direct/stable36/moodle-latest-36.tgz > moodle.tgz &&\
tar -xzf moodle.tgz &&\
rm moodle.tgz &&\
mv moodle/* . && rm -rf moodle && cd / &&\
sed -e "s/pgsql/mariadb/" \
  -e "s/username/moodle/" \
  -e "s/password/moodle/" \
  -e "s/localhost/db/" \
  -e "s|http://example.com/moodle|$MOODLE_URL|" \
  -e "s|/home/example/moodledata|/var/www/moodledata|" /var/www/html/config-dist.php > /var/www/html/config.php && \
chown -R www-data:www-data /var/www/html &&\
mkdir -p /var/log/php/errors &&\
touch /var/log/php/errors/php_error.log &&\
chmod 755 /var/log/php/errors/php_error.log &&\
chown www-data:www-data /var/log/php/errors/php_error.log &&\
echo "\nlog_errors = on" >> $PHP_INI_DIR/php.ini &&\
echo "display_errors = On" >> $PHP_INI_DIR/php.ini &&\
echo "display_startup_errors = On" >> $PHP_INI_DIR/php.ini &&\
echo "error_log = /var/log/php/errors/php_error.log" >> $PHP_INI_DIR/php.ini &&\
echo "<? phpinfo();" > /var/www/html/info.php &&\
echo "\nini_set ('display_errors', 'on');\nini_set ('log_errors', 'on');\nini_set ('display_startup_errors', 'on');\nini_set ('error_reporting', E_ALL);\n\$CFG->debug = 30719; // DEBUG_ALL, but that constant is not defined here." >> /var/www/html/config.php

# Fileinfo from startup_db
COPY --chown=www-data:www-data pix/icon.png /var/www/moodledata/filedir/d2/cb/d2cbcf06c8485af6bf3ef9f486fbec4b713642f5
