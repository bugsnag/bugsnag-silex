<?php

namespace Bugsnag\Silex;

use Bugsnag\Client;
use Bugsnag\Configuration;
use InvalidArgumentException;
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
     * Make a new bugsnag client instance.
     *
     * @param \Silex\Application $app
     *
     * @return \Bugsnag\Client
     */
    protected function makeClient(Application $app)
    {
        try {
            $config = $app['bugsnag.options'];
        } catch (InvalidArgumentException $e) {
            $config = [];
        }

        $key = isset($config['api_key']) ? $config['api_key'] : getenv('BUGSNAG_API_KEY');

        $guzzle = Client::makeGuzzle(isset($config['endpoint']) ? $config['endpoint'] : null);

        $client = new Client(new Configuration($key), $app['bugsnag.resolver'], $guzzle);

        if (!isset($config['callbacks']) || $config['callbacks']) {
            $client->registerDefaultCallbacks();
        }

        if (isset($config['strip_path'])) {
            $client->setStripPath($config['strip_path']);

            if (!isset($config['project_root'])) {
                $client->setProjectRoot($config['strip_path'].'/src');
            }
        } elseif (isset($config['project_root'])) {
            $client->setProjectRoot($config['project_root']);
        }

        $stage = getenv('SYMFONY_ENV') ?: null;
        $client->setReleaseStage($stage === 'prod' ? 'production' : $stage);
        $client->setAppType('Console');

        $client->setNotifier(array(
            'name' => 'Bugsnag Silex',
            'version' => static::VERSION,
            'url' => 'https://github.com/bugsnag/bugsnag-silex',
        ));

        if (isset($config['notify_release_stages']) && is_array($config['notify_release_stages'])) {
            $client->setNotifyReleaseStages($config['notify_release_stages']);
        }
        if (isset($config['filters']) && is_array($config['filters'])) {
            $client->setFilters($config['filters']);
        }

        return $client;
    }
}
