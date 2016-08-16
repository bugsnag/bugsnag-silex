<?php

// configure your app for the production environment

$app['twig.path'] = [__DIR__.'/../templates'];
$app['twig.options'] = ['cache' => __DIR__.'/../var/cache/twig'];

$app['bugsnag.options'] = ['api_key' => 'YOUR-API-KEY-HERE'];
