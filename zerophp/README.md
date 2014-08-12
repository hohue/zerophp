# ZeroPHP CMS built with Laravel 4

A Content Management System was built on [Laravel 4](http://laravel.com)

## Installation

* The ZeroPHP CMS can be installed via [Composer](http://getcomposer.org) by requiring the `laravel/framework`, `mews/purifier`, `kriswallsmith/assetic`, `intervention/image`, `bllim/datatables` package in your project's `composer.json`.

```json
{
    "require": {
        "zerophp/zerophp": "dev-master"
    }
}
```

Update your packages with ```composer update``` or install with ```composer install```.

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

* Create your databate and update ```app/config/database.php``` file.

* Creating Migrations:
``` php artisan migrate:make zerophp_install ```

* Update ```Y_m_d_his_zerophp_install.php``` (Ex: 2014_08_11_170120_zerophp_installphp) file:
```php
    public function up()
    {
        ZeroPHP\ZeroPHP\SystemInstall::up(Config::get('install.prev_version_zerophp_zerophp', 0));
    }

    public function down()
    {
        ZeroPHP\ZeroPHP\SystemInstall::down(Config::get('install.prev_version_zerophp_zerophp', 0));
    }
```

* Install Migrations:
```
php artisan migrate:install
php artisan migrate
```

* Update ```app/config/view.php``` file:

```php
    'paths' => array(
        __DIR__ . '/../../public/engine/views',
        __DIR__.'/../views'
    ),
```