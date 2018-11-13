<?php

namespace Grafikart\Csrf\Test;

use Grafikart\Csrf\CsrfMiddleware;
use Grafikart\Csrf\InvalidCsrfException;
use Grafikart\Csrf\NoCsrfException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CsrfMiddlewareTest extends TestCase
{
    private function makeMiddleware(&$session = [])
    {
        return new CsrfMiddleware($session);
    }

    private function makeRequest(string $method = 'GET', ?array $params = null)
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)
            ->getMock();
        $request->method('getMethod')->willReturn($method);
        $request->method('getParsedBody')->willReturn($params);

        return $request;
    }

    private function makeRequestHandler()
    {
        $delegate = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();
        $delegate->method('handle')->willReturn($this->makeResponse());

        return $delegate;
    }

    private function makeResponse()
    {
        return $this->getMockBuilder(ResponseInterface::class)->getMock();
    }

    public function testAcceptValidSession()
    {
        $a = [];
        $b = $this->getMockBuilder(\ArrayAccess::class)->getMock();
        $middlewarea = $this->makeMiddleware($a);
        $middlewareb = $this->makeMiddleware($b);
        $this->assertInstanceOf(CsrfMiddleware::class, $middlewarea);
        $this->assertInstanceOf(CsrfMiddleware::class, $middlewareb);
    }

    public function testRejectInvalidSession()
    {
        $this->expectException(\TypeError::class);
        $a = new \stdClass();
        $middlewarea = $this->makeMiddleware($a);
    }

    public function testGetPass()
    {
        $middleware = $this->makeMiddleware();
        $delegate = $this->makeRequestHandler();
        $delegate->expects($this->once())->method('handle');
        $middleware->process(
            $this->makeRequest('GET'),
            $delegate
        );
    }

    public function testPreventPost()
    {
        $middleware = $this->makeMiddleware();
        $delegate = $this->makeRequestHandler();
        $delegate->expects($this->never())->method('handle');
        $this->expectException(NoCsrfException::class);
        $middleware->process(
            $this->makeRequest('POST'),
            $delegate
        );
    }

    public function testPostWithValidToken()
    {
        $middleware = $this->makeMiddleware();
        $token = $middleware->generateToken();
        $delegate = $this->makeRequestHandler();
        $delegate->expects($this->once())->method('handle')->willReturn($this->makeResponse());
        $middleware->process(
            $this->makeRequest('POST', ['_csrf' => $token]),
            $delegate
        );
    }

    public function testPostWithInvalidToken()
    {
        $middleware = $this->makeMiddleware();
        $token = $middleware->generateToken();
        $delegate = $this->makeRequestHandler();
        $delegate->expects($this->never())->method('handle');
        $this->expectException(InvalidCsrfException::class);
        $middleware->process(
            $this->makeRequest('POST', ['_csrf' => 'aze']),
            $delegate
        );
    }

    public function testPostWithDoubleToken()
    {
        $middleware = $this->makeMiddleware();
        $token = $middleware->generateToken();
        $delegate = $this->makeRequestHandler();
        $delegate->expects($this->once())->method('handle')->willReturn($this->makeResponse());
        $middleware->process(
            $this->makeRequest('POST', ['_csrf' => $token]),
            $delegate
        );
        $this->expectException(InvalidCsrfException::class);
        $middleware->process(
            $this->makeRequest('POST', ['_csrf' => $token]),
            $delegate
        );
    }

    public function testLimitTokens()
    {
        $session = [];
        $middleware = $this->makeMiddleware($session);
        for ($i = 0; $i < 100; ++$i) {
            $token = $middleware->generateToken();
        }
        $this->assertCount(50, $session[$middleware->getSessionKey()]);
        $this->assertSame($token, $session[$middleware->getSessionKey()][49]);
    }
}
