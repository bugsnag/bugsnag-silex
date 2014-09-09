Bugsnag Middleware for Silex
==

The Bugsnag middleware for Silex integrates into Silex PHP applications.

[Bugsnag](https://bugsnag.com) captures errors in real-time from your web,
mobile and desktop applications, helping you to understand and resolve them
as fast as possible. [Create a free account](https://bugsnag.com) to start
capturing errors from your applications.

The Bugsnag middleware for Silex supports Silex 1.2+ and PHP 5.3+.

Installation
--

To get this middleware in to an existing project, the best way is to use
[Composer](http://getcomposer.org).

1. Add `bugsnag/bugsnag-silex` as a Composer dependency in your project's
   [`composer.json`][composer-json] file:

```json
{
  "require": {
    "bugsnag/bugsnag-silex": "*"
  }
}
```

2. If you haven't already, download and [install Composer][composer-download]:

```bash
curl -sS https://getcomposer.org/installer | php
```

3. [Install your Composer dependencies][composer-install]:

```bash
php composer.phar install
```

4. Set up [Composer's autoloader][composer-loader]:

```php
require_once 'vendor/autoload.php';
```

You're done!

[composer-json]: <http://getcomposer.org/doc/01-basic-usage.md#the-require-key>
    "More on the composer.json format"
[composer-download]: <http://getcomposer.org/doc/01-basic-usage.md#installation>
    "More detailed installation instructions on the Composer site"
[composer-install]: <http://getcomposer.org/doc/01-basic-usage.md#installing-dependencies>
    "More detailed instructions on the Composer site"
[composer-loader]: <http://getcomposer.org/doc/01-basic-usage.md#autoloading>
    "More information about the autoloader on the Composer site"

Example application
--

```php
<?php
require_once __DIR__.'/vendor/autoload.php';

$app = new Silex\Application();

$app->register(new Bugsnag\Silex\Provider\BugsnagServiceProvider, array(
    'bugsnag.options' => array(
        'apiKey' => '066f5ad3590596f9aa8d601ea89af845'
    )
));

$app->get('/hello/{name}', function($name) use($app) {
    throw new Exception("Hello!");
    return 'Hello '.$app->escape($name);
});

$app->run();
```
