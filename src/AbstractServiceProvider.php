<?php

namespace Bugsnag\Silex;

use Bugsnag\Callbacks\CustomUser;
use Bugsnag\Client;
use Bugsnag\Report;
use Bugsnag\Configuration;
use InvalidArgumentException;
use Silex\Application;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractServiceProvider
{
    /**
     * The package version.
     *
     * @var string
     */
    const VERSION = '2.2.0';

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

        if (!isset($config['user']) || $config['user']) {
            $this->setupUserDetection($client, $app);
        }

        $this->setupPaths($client, isset($config['strip_path']) ? $config['strip_path'] : null, isset($config['project_root']) ? $config['project_root'] : null);

        $env = getenv('SYMFONY_ENV') ?: null;
        $stage = isset($config['release_stage']) ? $config['release_stage'] : null;
        $client->setReleaseStage($stage ?: ($env === 'prod' ? 'production' : $env));
        $client->setHostname(isset($config['hostname']) ? $config['hostname'] : null);

        $client->setFallbackType('Console');
        $client->setAppType(isset($config['app_type']) ? $config['app_type'] : null);
        $client->setAppVersion(isset($config['app_version']) ? $config['app_version'] : null);
        $client->setBatchSending(isset($config['batch_sending']) ? $config['batch_sending'] : true);
        $client->setSendCode(isset($config['send_code']) ? $config['send_code'] : true);

        $client->setNotifier([
            'name' => 'Bugsnag Silex',
            'version' => static::VERSION,
            'url' => 'https://github.com/bugsnag/bugsnag-silex',
        ]);

        if (isset($config['notify_release_stages']) && is_array($config['notify_release_stages'])) {
            $client->setNotifyReleaseStages($config['notify_release_stages']);
        }
        if (isset($config['filters']) && is_array($config['filters'])) {
            $client->setFilters($config['filters']);
        }

        return $client;
    }

    /**
     * Setup user detection.
     *
     * @param \Bugsnag\Client    $client
     * @param \Silex\Application $app
     *
     * @return void
     */
    protected function setupUserDetection(Client $client, Application $app)
    {
        try {
            $tokens = $app['security.token_storage'];
            $checker = $app['security.authorization_checker'];
        } catch (InvalidArgumentException $e) {
            return;
        }

        $client->registerCallback(new CustomUser(function () use ($tokens, $checker) {
            $token = $tokens->getToken();

            if (!$token || !$checker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
                return;
            }

            $user = $token->getUser();

            if ($user instanceof UserInterface) {
                return ['id' => $user->getUsername()];
            }

            return ['id' => (string) $user];
        }));
    }

    /**
     *
     */
    protected function autoNotify(Client $client, $exception, $callback=null)
    {
        $report = Report::FromPHPThrowable(
            $client->getConfig(),
            $exception,
            Report::MIDDLEWARE_HANDLER,
            [
                'name' => 'silex'
            ]);
        $client->notify($report, $callback);
    }

    /**
     * Setup the client paths.
     *
     * @param \Bugsnag\Client $client
     * @param string|null     $strip
     * @param string|null     $project
     *
     * @return void
     */
    protected function setupPaths(Client $client, $strip, $project)
    {
        if ($strip) {
            $client->setStripPath($strip);

            if (!$project) {
                $client->setProjectRoot("{$strip}/src");
            }

            return;
        }

        $base = realpath(__DIR__.'/../../../../');

        if ($project) {
            if ($base && substr($project, 0, strlen($base)) === $base) {
                $client->setStripPath($base);
            }

            $client->setProjectRoot($project);

            return;
        }

        if ($base) {
            $client->setStripPath($base);

            if ($root = realpath("{$base}/src")) {
                $client->setProjectRoot($root);
            }
        }
    }
}
