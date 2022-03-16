ARG php_version=8.1

FROM 3liz/liz-php-cli:${php_version}

ARG php_version
ARG DEBIAN_FRONTEND=noninteractive
ENV PHP_VERSION=${php_version}

COPY appctl.sh /bin/
RUN chmod 755 /bin/appctl.sh

CMD ["tail", "-f", "/var/log/php_cli_errors.log"]