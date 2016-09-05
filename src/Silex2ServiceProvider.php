<?php

namespace Bugsnag\Silex;

use Bugsnag\Silex\Request\SilexResolver;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Application;
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

        $app->before(function (Request $request) use ($app) {
            $app['bugsnag']->setFallbackType('HTTP');
            $app['bugsnag.resolver']->set($request);
        }, Application::EARLY_EVENT);
    }
}
