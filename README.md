<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

laravel models after existing tables:https://stackoverflow.com/questions/20892978/map-existing-database-table-for-laravel

https://gist.github.com/rcknr/47d3a175fb32647742850666dbdbc086


custom hasher:https://gist.github.com/rcknr/47d3a175fb32647742850666dbdbc086

modules comand https://laravelmodules.com/docs/v11/artisan-commands

after clone this repository:
cp .env.example .env
-update mysql credentials
composer install
php artisan key:generate
php artisan config:cache

 git push origin HEAD:master

 ////php artisan module:make-migration alter_table_translations_change_value Translations
 php artisan module:seed Brokers
php artisan module:migrate translations
php artisan migrate:rollback --batch=8
 php artisan l5-swagger:generate
 php artisan db:seed --class=\\Modules\\Brokers\\Database\\Seeders\\DatabaseSeeder

php artisan route:list
php artisan cache:clear

php artisan module:make-model Broker --controller --factory --seed Brokers

php artisan module:make-migration create_translations_table Translations
php artisan module:make-factory BrokerTypeFactory Brokers

php artisan make:seeder UserSeeder
php artisan module:make-seed OtionsCategories Brokers

//rollback latest migration
php artisan module:migrate-rollback --pretend Translations
 php artisan migrate:refresh --step=1
php artisan migrate --path=/Modules/Translations/database/migrations/2025_03_01_202922_create_locale_resources_table.php
//{"main_header": "This is the main header text"}

{{PATH}}/brokers?language[eq]=ro&page=1&columns[in]=position_home,position_list,short_payment_options,trading_fees&filters[in]=a,b,c



[
    'broker_static_columns'=>[
    'logo'=>'Sigla',
    'trading_name'=>"Denumire comerciala",
    'home_url'=>"Link acasa",
    'overall_rating'=>"Rating general",
    'user_rating'=>"Rating utilizator",
    'support_options'=>"Optiuni suport",
    'account_type'=>"Tipul contului",
    'trading_instruments'=>"Instrumente de tranzactionare",
    'account_currencies'=>"Moneda cont",
    'language'=>"limba",
    'default_language'=>"Limba implicita"
    ],
    'broker_ext_columns'=>[
        "regulators"=>"Regulatori"
    ],
    'filters'=>[
        'offices'=>'Birouri',
        'headquarters'=>'Sediu',
        'regulators'=>'Regulatori'
    ]
]

[
    'broker_static_columns'=>[
    'logo'=>'Logo',
    'trading_name'=>"Trading Name",
    'home_url'=>"Home URL",
    'overall_rating'=>"Overall Rating",
    'user_rating'=>"User Rating",
    'support_options'=>"Support Options",
    'account_type'=>"Account Type",
    'trading_instruments'=>"Trading Instruments",
    'account_currencies'=>"Account Currencies",
    'language'=>"Language",
    'default_language'=>"Default Language"
    ],
    'broker_ext_columns'=>[
        "regulators"=>"Regulators"
    ],
    'filters'=>[
        'offices'=>'Offices',
        'headquarters'=>'Headquarters',
        'regulators'=>'Regulators'
    ]
]
//https://www.youtube.com/watch?v=MF0jFKvS4SI despre api rest design







https://laracasts.com/discuss/channels/laravel/progress-bars-on-other-class

docker exec -it backend-laravel-1 bash -c "chown -R www-data:www-data /var/www/html && chmod -R g+w /var/www/html"


//to load magic imports
docker exec -it backend-laravel-1 php artisan migrate:fresh
docker exec -it backend-laravel-1 php artisan app:magic-import

 docker exec -it backend-laravel-1 php artisan db:seed --class=\\Modules\\Brokers\\Database\\Seeders\\DatabaseSeeder

docker exec -it backend-laravel-1 php artisan migrate:rollback --step=2

docker exec -it backend-laravel-1 php artisan migrate --path=Modules/Auth/database/migrations

get all routes for a path
php artisan route:list --path=platform-users --except-vendor --json | python3 -m json.tool


echo "Run these commands in your terminal (not here, as sudo access is needed):

# 1. Remove the broken installation
sudo rm -f /usr/local/bin/cursor
sudo rm -rf /opt/cursor
rm -rf ~/projects/squashfs-root

# 2. Download and reinstall Cursor properly
# Visit: https://cursor.sh/ or download the AppImage/tar.gz

# 3. Or if you have the AppImage extracted in a different location, create proper symlinks"
