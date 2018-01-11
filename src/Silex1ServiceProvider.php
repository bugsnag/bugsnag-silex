<?php

namespace Bugsnag\Silex;

use Bugsnag\Silex\Request\SilexResolver;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Silex\Provider\SessionServiceProvider;
use Symfony\Component\HttpFoundation\Request;

class Silex1ServiceProvider extends AbstractServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the application.
     *
     * @param \Silex\Application $app
     *
     * @return void
     */
    public function register(Application $app)
    {
        $app['bugsnag.resolver'] = $app->share(function () {
            return new SilexResolver();
        });

        $app['bugsnag'] = $app->share(function () use ($app) {
            return $this->makeClient($app);
        });

        $app['bugsnag.notifier'] = function($error) use ($app) {
            return function($error) use ($app) {
                $this->autoNotify($app['bugsnag'], $error);
            };
        };

        $app->register(new SessionServiceProvider());

        $app->before(function (Request $request) use ($app) {
            $app['bugsnag']->setFallbackType('HTTP');
            $app['bugsnag.resolver']->set($request);
            $app['bugsnag']->startSession();
        }, Application::EARLY_EVENT);
    }

    /**
     * Bootstraps the application.
     *
     * @param \Silex\Application $app
     *
     * @return void
     */
    public function boot(Application $app)
    {
        //
    }
}
