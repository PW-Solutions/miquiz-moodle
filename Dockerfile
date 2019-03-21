FROM axyz/apache-php

ARG MOODLE_URL

RUN apt-get install nano &&\
mkdir /var/www/htdocs && mkdir /var/www/moodledata && mkdir -p /var/www/htdocs/www/calo && \
chown -R www-data:www-data /var/www/moodledata && cd /var/www/htdocs && \
curl https://download.moodle.org/download.php/direct/stable33/moodle-latest-33.tgz > moodle.tgz && \
tar -xzf moodle.tgz && \
rm moodle.tgz && \
mv moodle/* . && rm -rf moodle && cd / && \
chown -R www-data:www-data /var/www && \
sed -e "s/pgsql/mariadb/" \
  -e "s/username/moodle/" \
  -e "s/password/moodle/" \
  -e "s/localhost/db/" \
  -e "s|http://example.com/moodle|$MOODLE_URL|" \
  -e "s|/home/example/moodledata|/var/www/moodledata|" /var/www/htdocs/config-dist.php > /var/www/htdocs/config.php && \
chown www-data:www-data /var/www/htdocs/config.php &&\
mkdir -p /var/log/php/errors &&\
touch /var/log/php/errors/php_error.log &&\
chmod 755 /var/log/php/errors/php_error.log &&\
chown www-data:www-data /var/log/php/errors/php_error.log &&\
echo "\nlog_errors = on" >> /usr/local/etc/php/php.ini &&\
echo "display_errors = On" >> /usr/local/etc/php/php.ini &&\
echo "display_startup_errors = On" >> /usr/local/etc/php/php.ini &&\
echo "error_log = /var/log/php/errors/php_error.log" >> /usr/local/etc/php/php.ini &&\
echo "<? phpinfo();" > /var/www/htdocs/info.php &&\
echo "\nini_set ('display_errors', 'on');\nini_set ('log_errors', 'on');\nini_set ('display_startup_errors', 'on');\nini_set ('error_reporting', E_ALL);\n\$CFG->debug = 30719; // DEBUG_ALL, but that constant is not defined here." >> /var/www/htdocs/config.php

# Fileinfo from startup_db
COPY --chown=www-data:www-data pix/icon.png /var/www/moodledata/filedir/d2/cb/d2cbcf06c8485af6bf3ef9f486fbec4b713642f5
