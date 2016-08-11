<?php

namespace Bugsnag\Silex;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

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
        $this->registerServices($app);

        $this->registerCallbacks($app);
    }
}
