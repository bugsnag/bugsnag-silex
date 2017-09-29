<?php

namespace Bugsnag\Silex\Tests\Request;


use Bugsnag\Client;
use Bugsnag\Report;
use Silex\Application;
use Bugsnag\Silex\Silex2ServiceProvider;
use GrahamCampbell\TestBenchCore\MockeryTrait;
use PHPUnit_Framework_TestCase as TestCase;
use Mockery;

class RequestStub {
    const MIDDLEWARE_HANDLER = "middleware_handler";
}

class AutoNotifyTest extends TestCase
{
    use MockeryTrait;

    public function testAutoNotifyRespondsCorrectly()
    {
        # Create mocks
        $report = Mockery::namedMock(Report::class, RequestStub::class);
        $client = Mockery::mock(Client::class);
        $app = Mockery::mock(Application::class);

        # Create test objects
        $exception = new \Exception("Test");

        $app->shouldReceive('offsetSet')->with(Mockery::any(), Mockery::any())->andReturnUsing(
            function($key, $value) use ($app, $exception) {
                if ($key == 'bugsnag.notifier') {
                    $notifyFunc = call_user_func($value, $app);
                    call_user_func($notifyFunc, $exception);
                }
            }
        );
        $app->shouldReceive('before');
        $app->shouldReceive('offsetGet')->andReturnUsing(
            function($key) use ($client) {
                if ($key == 'bugsnag') {
                    return $client;
                }
            }
        );
        $report->shouldReceive('fromPHPThrowable')
            ->with('config', $exception, true, ['type' => 'unhandledExceptionMiddleware', 'attributes' => ['framework' => 'Silex']])
            ->once()
            ->andReturn($report);
        $client->shouldReceive('getConfig')->once()->andReturn('config');
        $client->shouldReceive('notify')->once()->with($report, Mockery::any());

        # Initiate test
        $serviceProvider = new Silex2ServiceProvider();
        $serviceProvider->register($app);
    }
    
}
