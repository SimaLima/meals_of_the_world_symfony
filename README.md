# Meals of the World
This is multilingual application made with Symfony.<br>
It uses DoctrineExtensions/Translatable package to handle translations.<br>
Its purpose is to:
- validate & process form request
- filter items(meals) using parameters from submitted form
- return JSON response

## Requirements:
 - <b>PHP 7.4</b> (required by Symfony5)
 - Symfony CLI (optional)

## Installation (requires composer)
1. extract git repository
2. create new database in admin (phpmyadmin) and configure .env
3. locate to project root folder (meals_of_the_world_v3-main) in command line and run commands:
```sh
composer install
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```
Start server:
```sh
symfony server:start (or some other server command...)
```
And lastly open in browser Local Web Address.

## Versions:
Composer: 2.0.9<br>
Symfony: 5.2.3<br>
PHP: 7.4.15<br>
MySQL: 10.4.11-MariaDB
