FROM axyz/apache-php

RUN mkdir /var/www/htdocs && mkdir /var/www/moodledata && mkdir -p /var/www/htdocs/www/calo && \
chown -R www-data:www-data /var/www/moodledata && cd /var/www/htdocs && \
curl -O https://download.moodle.org/download.php/direct/stable33/moodle-latest-33.tgz && \
tar -xzf moodle-latest-33.tgz && \
rm moodle-latest-33.tgz && \
mv moodle/* . && rm -rf moodle && cd / && \
chown -R www-data:www-data /var/www && \
sed -e "s/pgsql/mariadb/" \
  -e "s/username/moodle/" \
  -e "s/password/moodle/" \
  -e "s/localhost/db/" \
  -e "s/example.com\/moodle/129.206.245.121:8080/" \
  -e "s/\/home\/example\/moodledata/\/var\/www\/moodledata/" /var/www/htdocs/config-dist.php > /var/www/htdocs/config.php && \
chown www-data:www-data /var/www/htdocs/config.php
