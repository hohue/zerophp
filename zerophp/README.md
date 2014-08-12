# ZeroPHP CMS built with Laravel 4

A Content Management System was built on [Laravel 4](http://laravel.com)

## Installation

* Install fresh Laravel project:
``` laravel new your_project_name ```

* Update `composer.json` file:

```json
{
    "require": {
        "zerophp/zerophp": "dev-master"
    }
}
```

Update your packages with: ```composer update```

## Routes

* Update ```app/routes.php``` file:

```php
Route::get('{slug}', function () {
    $ZeroPHP = new \ZeroPHP\ZeroPHP\ZeroPHP;
    return $ZeroPHP->bootstrap();
})->where('slug', '([A-z0-9-\/_.]+)?');

Route::post('{slug}', array(function () {
    $ZeroPHP = new \ZeroPHP\ZeroPHP\ZeroPHP;
    return $ZeroPHP->bootstrap('post');
}))->where('slug', '([A-z0-9-\/_.]+)?');

App::error(function ($exception, $code) {
    switch ($code) {
        case 403:
            return View::make('layouts/page-403');
            
        case 404:
            return View::make('layouts/page-404');

        default:
            return View::make('layouts/page-500');
    }
});
```

## Registering the Package

* Update ```app/config/app.php``` file:

```php
    'providers' => array(
        //...

        'Bllim\Datatables\DatatablesServiceProvider',
        'Mews\Purifier\PurifierServiceProvider',
    ),
    'aliases' => array(
        //...

        'Datatables'        => 'Bllim\Datatables\Facade\Datatables',
        'Purifier'          => 'Mews\Purifier\Facades\Purifier',
    ),
```

## Database Configuration

* Create your database and update ```app/config/database.php``` file.

* Creating Migrations:
``` php artisan migrate:make zerophp_install ```

* Update ```app/database/migrations/Y_m_d_his_zerophp_install.php``` (Ex: app/database/migrations/2014_08_11_170120_zerophp_installphp) file:

```php
    public function up()
    {
        \ZeroPHP\ZeroPHP\SystemInstall::up(0));
    }

    public function down()
    {
        \ZeroPHP\ZeroPHP\SystemInstall::down(0));
    }
```

* Update ```app/database/migrations/Y_m_d_his_zerophp_install.php``` file for ZeroPHP CMS Update:

```php
    public function up()
    {
        \ZeroPHP\ZeroPHP\SystemInstall::up(zerophp_variable_get('install prev_version_zerophp_zerophp', 0));
    }

    public function down()
    {
        \ZeroPHP\ZeroPHP\SystemInstall::down(zerophp_variable_get('install prev_version_zerophp_zerophp', 0));
    }
```

* Install Migrations:

```
php artisan migrate:install
php artisan migrate
```

## Assets Configuration

* Public assets:
``` php artisan asset:publish --path=vendor/zerophp/zerophp/public zerophp/zerophp ```

* Update ```app/config/view.php``` file:

```php
    'paths' => array(
        __DIR__ . '/../../public/packages/zerophp/zerophp/themes/engine/views',
        __DIR__.'/../views'
    ),
```

## Configuration

```
php artisan config:publish bllim/datatables
php artisan config:publish --path=vendor/zerophp/zerophp/config zerophp/zerophp
```