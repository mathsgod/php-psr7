<?php

declare(strict_types=1);
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

use PHPUnit\Framework\TestCase;

use PHP\Psr7\ServerRequest;

final class ServerRequestTest extends TestCase
{

    public function test_create()
    {
        $r = new ServerRequest;
        $this->assertInstanceOf(ServerRequest::class, $r);
    }
}
