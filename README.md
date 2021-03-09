# Meals of the World
This is multilingual application made with Symfony.<br>
Its purpose is to:
- validate & process form request
- filter items(meals) using parameters from submitted form
- return JSON response

## Requirements:
 - PHP 7.4 (required by Symfony)
 - Symfony CLI (optional)

## Installation (requires composer)
1. extract git repository
2. create new database in admin (phpmyadmin) and configure .env
3. locate to project root folder (meals_of_the_world_v3) in command line and run commands:
```sh
composer install
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```
Start server:
```sh
symfony server:start (or some other server command...)
```
Open in browser: http://127.0.0.1:8000

## Versions:
Composer: 2.0.9<br>
Symfony: 5.2.3<br>
PHP: 7.4.15<br>
MySQL: 10.4.11-MariaDB

## Notes:
Database relationships can be seen in screenshot-database.
