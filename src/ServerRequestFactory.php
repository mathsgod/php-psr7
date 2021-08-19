<?php

namespace PHP\Psr7;

use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\ServerRequestFactory as DiactorosServerRequestFactory;

class ServerRequestFactory extends DiactorosServerRequestFactory
{
    public static function fromGlobals(
        array $server = null,
        array $query = null,
        array $body = null,
        array $cookies = null,
        array $files = null
    ): ServerRequest {
        $request = parent::fromGlobals($server, $query, $body, $cookies, $files);

        if (strpos($request->getHeaderLine("Content-Type"), "application/json") !== false) {
            $body = $request->getBody()->getContents();
            $request = $request->withParsedBody(json_decode($body, true));
        }

        return $request;
    }
}
