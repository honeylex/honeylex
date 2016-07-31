################################################################################
# Base image
################################################################################

FROM nginx

################################################################################
# Build instructions
################################################################################

# Remove default nginx configs.
RUN rm -f /etc/nginx/conf.d/*

# Install packages
RUN apt-get update && apt-get install -my \
  supervisor \
  curl \
  git \
  wget \
  php5-cli \
  php5-curl \
  php5-fpm \
  php5-gd \
  php5-imagick \
  php5-intl \
  php5-json \
  php5-memcached \
  php5-mcrypt \
  php5-xdebug \
  php5-xsl

# Ensure that PHP5 FPM is run as root.
RUN sed -i "s/user = www-data/user = root/" /etc/php5/fpm/pool.d/www.conf
RUN sed -i "s/group = www-data/group = root/" /etc/php5/fpm/pool.d/www.conf

# Pass all docker environment
RUN sed -i '/^;clear_env = no/s/^;//' /etc/php5/fpm/pool.d/www.conf

# Prevent PHP Warning: 'xdebug' already loaded.
# XDebug loaded with the core
RUN sed -i '/.*xdebug.so$/s/^/;/' /etc/php5/mods-available/xdebug.ini

# Add configuration files
COPY conf/nginx/nginx.conf /etc/nginx/
COPY conf/supervisord.conf /etc/supervisor/conf.d/
COPY conf/php/php-cli.ini /etc/php5/cli/conf.d/40-custom.ini
COPY conf/php/php-fpm.ini /etc/php5/fpm/conf.d/40-custom.ini

# Add composer
COPY scripts/composer-install.sh /tmp/
RUN chmod +x /tmp/composer-install.sh
RUN /tmp/composer-install.sh
RUN mkdir -m 777 /.composer

################################################################################
# Ports
################################################################################

EXPOSE 80 443

################################################################################
# Volumes
################################################################################

VOLUME ["/var/www"]

################################################################################
# Entrypoint
################################################################################

ENTRYPOINT ["/usr/bin/supervisord"]