# Env

-   Laravel 8.14.0
-   php 7.4.3
-   mysql 8.0.22
-   composer 1.10.1

# POSTMAN EXPORT FILE

`gmg-back-api.postman_collection.json`

# INSTALL

Run `composer install`

create mysql database `gmg_app`
create mysql database for testing `gmg_app_testing`

Run `cp .env.example .env`
Set if needed `.env` and `.env.testing`

Run `php artisan migrate`
Run `php artisan migrate --env=testing`

# SERVE

Run `php artisan serve`

# URL

`http://localhost:8000/`

# TEST

Run `php artisan test`
