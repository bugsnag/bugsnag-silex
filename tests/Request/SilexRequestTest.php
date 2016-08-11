<?php

namespace Bugsnag\Silex\Tests\Request;

use Bugsnag\Silex\Request\SilexRequest;
use Bugsnag\Silex\Request\SilexResolver;
use Bugsnag\Request\NullRequest;
use Bugsnag\Request\RequestInterface;
use GrahamCampbell\TestBenchCore\MockeryTrait;
use Symfony\Component\HttpFoundation\Request;
use Mockery;
use PHPUnit_Framework_TestCase as TestCase;

class SilexRequestTest extends TestCase
{
    use MockeryTrait;

    public function testCanResolveNullRequest()
    {
        $resolver = new SilexResolver();

        $request = $resolver->resolve();

        $this->assertInstanceOf(RequestInterface::class, $request);
        $this->assertInstanceOf(NullRequest::class, $request);
    }

    public function testCanResolveSilexRequest()
    {
        $resolver = new SilexResolver();

        $resolver->setRequest(new Request());

        $request = $resolver->resolve();

        $this->assertInstanceOf(RequestInterface::class, $request);
        $this->assertInstanceOf(SilexRequest::class, $request);
    }
}
