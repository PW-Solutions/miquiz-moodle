FROM axyz/apache-php

ARG MOODLE_URL

RUN mkdir /var/www/htdocs && mkdir /var/www/moodledata && mkdir -p /var/www/htdocs/www/calo && \
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
chown www-data:www-data /var/www/htdocs/config.php
