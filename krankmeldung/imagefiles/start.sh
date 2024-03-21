#!/bin/bash
sudo /usr/sbin/cron
until php artisan migrate
do
    sleep 1
done
php artisan app:restore
php artisan optimize
php artisan serve --host=0.0.0.0
