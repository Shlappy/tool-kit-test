# syntax=docker/dockerfile:1.7-labs
FROM php:8.4-fpm

# set main params
ARG BUILD_ARGUMENT_ENV=dev
ENV ENV=$BUILD_ARGUMENT_ENV
ENV APP_HOME /var/www/html
ARG HOST_UID=1000
ARG HOST_GID=1000
ENV USERNAME=www-data
ARG INSIDE_DOCKER_CONTAINER=1
ENV INSIDE_DOCKER_CONTAINER=$INSIDE_DOCKER_CONTAINER

# check environment
RUN if [ "$BUILD_ARGUMENT_ENV" = "default" ]; then echo "Set BUILD_ARGUMENT_ENV in docker build-args like --build-arg BUILD_ARGUMENT_ENV=dev" && exit 2; \
    elif [ "$BUILD_ARGUMENT_ENV" = "dev" ]; then echo "Building development environment."; \
    elif [ "$BUILD_ARGUMENT_ENV" = "prod" ]; then echo "Building production environment."; \
    else echo "Set correct BUILD_ARGUMENT_ENV in docker build-args like --build-arg BUILD_ARGUMENT_ENV=dev. Available choices are dev,prod." && exit 2; \
    fi

# install all the dependencies and enable PHP modules
RUN apt-get update && apt-get upgrade -y && apt-get install -y \
      bash-completion \
      procps \
      nano \
      git \
      unzip \
      libicu-dev \
      libpq-dev \
      postgresql-client \
      zlib1g-dev \
      libxml2 \
      libxml2-dev \
      libreadline-dev \
      sudo \
      libzip-dev \
      wget \
    && docker-php-ext-configure intl \
    && docker-php-ext-install \
      pdo \
      pgsql \
      pdo_pgsql \
      intl \
      zip \
    && rm -rf /tmp/* \
    && rm -rf /var/list/apt/* \
    && rm -rf /var/lib/apt/lists/* \
    && apt-get clean

# create document root, fix permissions for www-data user and change owner to www-data
RUN mkdir -p $APP_HOME/public && \
    mkdir -p /home/$USERNAME && chown $USERNAME:$USERNAME /home/$USERNAME \
    && usermod -o -u $HOST_UID $USERNAME -d /home/$USERNAME \
    && groupmod -o -g $HOST_GID $USERNAME \
    && chown -R ${USERNAME}:${USERNAME} $APP_HOME

# put php config for Symfony
COPY ./docker/$BUILD_ARGUMENT_ENV/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY ./docker/$BUILD_ARGUMENT_ENV/php.ini /usr/local/etc/php/php.ini

# install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN chmod +x /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER 1

# Enable Composer autocompletion
RUN composer completion bash > /etc/bash_completion.d/composer

# set working directory
WORKDIR $APP_HOME

USER ${USERNAME}

# Add necessary stuff to bash autocomplete
RUN echo 'source /usr/share/bash-completion/bash_completion' >> /home/${USERNAME}/.bashrc \
    && echo 'alias console="/var/www/html/bin/console"' >> /home/${USERNAME}/.bashrc

# copy source files
COPY --chown=${USERNAME}:${USERNAME} . $APP_HOME/

# install all PHP dependencies
RUN if [ "$BUILD_ARGUMENT_ENV" = "dev" ]; then COMPOSER_MEMORY_LIMIT=-1 composer install --optimize-autoloader --no-interaction --no-progress; \
    else export APP_ENV=$BUILD_ARGUMENT_ENV && COMPOSER_MEMORY_LIMIT=-1 composer install --optimize-autoloader --no-interaction --no-progress --no-dev; \
    fi

# create cached config file .env.local.php in case staging/prod environment
RUN if [ "$BUILD_ARGUMENT_ENV" = "prod" ]; then composer dump-env $BUILD_ARGUMENT_ENV; \
    fi

USER root
