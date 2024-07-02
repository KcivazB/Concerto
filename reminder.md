https://symfony.com/doc/current/deployment.html



composer require symfony/requirements-checker


composer dump-env prod


composer install --no-dev --optimize-autoloader

php bin/console cache:clear


