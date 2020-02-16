<?php

namespace Bugsnag\Silex\Tests\Request;


use Bugsnag\Client;
use Bugsnag\Report;
use Bugsnag\Silex\Silex1ServiceProvider;
use Bugsnag\Silex\Silex2ServiceProvider;
use Exception;
use GrahamCampbell\TestBenchCore\MockeryTrait;
use Mockery;
use Pimple\ServiceProviderInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Silex\Application;

class AutoNotifyTest extends TestCase
{
    use MockeryTrait;

    public function testAutoNotify()
    {
        // Create mocks
        $report = Mockery::namedMock(Report::class, RequestStub::class);
        $client = Mockery::mock(Client::class);
        $app = Mockery::mock(Application::class);

        // Create test objects
        $exception = new Exception("Test");

        $app->shouldReceive('offsetSet')->with(Mockery::any(), Mockery::any())->andReturnUsing(
            function($key, $value) use ($app, $exception) {
                if ($key == 'bugsnag.notifier') {
                    $notifyFunc = call_user_func($value, $app);
                    call_user_func($notifyFunc, $exception);
                }
            }
        );
        $app->shouldReceive('share');
        $app->shouldReceive('before');
        $app->shouldReceive('offsetGet')->andReturnUsing(
            function($key) use ($client) {
                if ($key == 'bugsnag') {
                    return $client;
                }
            }
        );
        $report->shouldReceive('fromPHPThrowable')
            ->with('config', $exception)
            ->once()
            ->andReturn($report);
        $report->shouldReceive('setUnhandled')->once()->with(true);
        $report->shouldReceive('setSeverityReason')->once()->with([
            'type' => 'unhandledExceptionMiddleware',
            'attributes' => ['framework' => 'Silex'],
        ]);
        $client->shouldReceive('getConfig')->once()->andReturn('config');
        $client->shouldReceive('notify')->once()->with($report, Mockery::any());

        // Initiate test
        $serviceProvider = self::getSilexServiceProvider();
        $serviceProvider->register($app);
    }

    private static function getSilexServiceProvider()
    {
        if (interface_exists(ServiceProviderInterface::class)) {
            return new Silex2ServiceProvider();
        }

        return new Silex1ServiceProvider();
    }
}

class RequestStub
{
    const MIDDLEWARE_HANDLER = "middleware_handler";
}
