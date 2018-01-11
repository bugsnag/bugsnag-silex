<?php

namespace Bugsnag\Silex;

use Bugsnag\Silex\Request\SilexResolver;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Application;
use Silex\Provider\SessionServiceProvider;
use Symfony\Component\HttpFoundation\Request;

class Silex2ServiceProvider extends AbstractServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the container.
     *
     * @param \Pimple\Container $app
     *
     * @return void
     */
    public function register(Container $app)
    {
        $app['bugsnag.resolver'] = function () {
            return new SilexResolver();
        };

        $app['bugsnag'] = function () use ($app) {
            return $this->makeClient($app);
        };

        $app['bugsnag.notifier'] = function() use ($app) {
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
}
