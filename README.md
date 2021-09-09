## Install

- composer install
- copy ./.env.example ./.env
- php artisan key:generate
- php artisan storage:link
- php artisan queue:table
- php artisan migrate:fresh --seed
