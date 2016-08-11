<?php

namespace Bugsnag\Silex;

use Bugsnag\Client;
use Bugsnag\Silex\Request\SilexResolver;
use Exception;
use Silex\Application;

abstract class AbstractServiceProvider
{
    /**
     * The package version.
     *
     * @var string
     */
    const VERSION = '2.0.0';

    /**
     * Registers the bugsnag services.
     *
     * @param \Silex\Application $app
     *
     * @return void
     */
    protected function registerServices(Application $app)
    {
        $app['bugsnag.resolver'] = $app->share(function () use ($app) {
            return new SilexResolver();
        });

        $app['bugsnag'] = $app->share(function () use ($app) {
            $config = $app['bugsnag.options'];

            $key = isset($config['apiKey']) ? $config['apiKey'] : null;

            $guzzle = Client::makeGuzzle(isset($config['endpoint']) ? $config['endpoint'] : null, $options);

            $client = new Client(new Configuration($key, $endpoint), $app['bugsnag.resolver'], $guzzle);

            $client->registerDefaultCallbacks();

            $client->setNotifier(array(
                'name' => 'Bugsnag Silex',
                'version' => static::VERSION,
                'url' => 'https://github.com/bugsnag/bugsnag-silex',
            ));

            if (isset($config['filters']) && is_array($config['filters'])) {
                $client->setFilters($config['filters']);
            }

            return $client;
        });
    }

    /**
     * Registers the bugsnag callbacks.
     *
     * @param \Silex\Application $app
     *
     * @return void
     */
    protected function registerCallbacks(Application $app)
    {
        $app->before(function ($request) use ($app) {
            $app['bugsnag.resolver']->set($request);
        });

        $app->error(function (Exception $error, $code) use ($app) {
            $app['bugsnag']->notifyException($error, $params);
        });
    }
}
