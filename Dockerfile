FROM php:8.2-fpm
ARG user
ARG uid
ARG cron
ARG appname
ARG LANG
ARG LANGUAGE
ARG TZ
ENV LANG $LANG
ENV LANGUAGE $LANGUAGE
ENV LC_ALL $LANG
RUN apt update && apt install -y locales
#### Language
RUN echo "locales locales/default_environment_locale select $LANG" | debconf-set-selections
RUN echo "locales locales/locales_to_be_generated multiselect $LANG UTF-8" | debconf-set-selections
RUN rm "/etc/locale.gen"
RUN dpkg-reconfigure --frontend noninteractive locales
RUN locale-gen $LANG
#### Timezone
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
#### Install Packets
RUN apt update && apt install -y \
    mc \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    cron \
    sudo \
    default-mysql-client
#### Cleanup
RUN apt clean && rm -rf /var/lib/apt/lists/*
#### Install Composer
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
#### Add User
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer
RUN chown -R $user:$user /home/$user
RUN chown -R $user:$user /var/www
#### Get cron running
RUN gpasswd -a $user sudo
RUN echo "$user\tALL=(ALL:ALL) NOPASSWD: /usr/sbin/cron" >> /etc/sudoers
WORKDIR /var/www
USER $user
#### Install Laravel
RUN composer create-project laravel/laravel $appname
RUN ln -s ./$appname ./laravel
#### Install App
COPY --chown=$user imagefiles /var/www/$appname/
COPY --chown=$user .env /var/www/$appname/.env
#### Get mc running nonroot
COPY --chown=$user ./config/.bashrc /home/$user/.bashrc
WORKDIR /var/www/$appname
#### Set cronjobs
RUN crontab -l | { cat; echo "${cron} /usr/local/bin/php /var/www/$appname/artisan schedule:run >> /var/www/$appname/cron.log 2>&1"; } | crontab -
ENTRYPOINT ["./start.sh"]
EXPOSE 8000
