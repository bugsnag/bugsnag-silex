<?php

namespace Bugsnag\Silex;

use Silex\Application;
use Silex\ServiceProviderInterface;

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
        $this->registerServices($app);

        $this->registerCallbacks($app);
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
