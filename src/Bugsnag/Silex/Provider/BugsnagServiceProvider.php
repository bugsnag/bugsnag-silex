<?php

namespace Bugsnag\Silex\Provider;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class BugsnagServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['bugsnag'] = function ($app) {
            $client = \Bugsnag\Client::make($app['bugsnag.options']['apiKey']);
            \Bugsnag\Handler::register($client);

            return $client;
        };

        $app->error(
          function (\Exception $error, Request $request) use ($app) {
              $params['request'] = array(
                'params' => $request->query->all(),
                'requestFormat' => $request->getRequestFormat(),
              );

              if ($session = $request->getSession()) {
                  $params['session'] = $session->all();
              }

              if ($cookies = $request->cookies->all()) {
                  $params['cookies'] = $cookies;
              }

              $app['bugsnag']->registerCallback(
                function ($report) use ($params, $error) {
                    $report->setMetaData($params);
                }
              );

              $app['bugsnag']->notifyException($error);
          }
        );
    }

    public function boot(Application $app)
    {
        //
    }
}
