<?php

namespace PHP\Psr7;

use Psr\Http\Message\ServerRequestInterface;

class ServerRequest extends Request implements ServerRequestInterface
{
    protected $attributes = [];
    protected $cookies;
    protected $uploadedFiles = [];

    public function __construct()
    {
        $server = $this->getServerParams();

        $this->method = $server["REQUEST_METHOD"];
        $headers = getallheaders();
        foreach ($headers as $name => $value) {
            $this->headers[$name] = explode(",", $value);
        }

        $protocol = explode("/", $server["SERVER_PROTOCOL"], 2);
        $this->protocolVersion = $protocol[1];

        $uri = new Uri();
        if ($scheme = $server["REQUEST_SCHEME"]) {
            $uri = $uri->withScheme($scheme);
        }

        if (isset($server['SERVER_NAME'])) {
            $uri = $uri->withHost($server['SERVER_NAME']);
        } elseif (isset($server['HTTP_HOST'])) {
            $uri = $uri->withHost($server['HTTP_HOST']);
        }

        if ($port = $server["SERVER_PORT"]) {
            $uri = $uri->withPort(intval($port));
        }

        if ($server["PHP_AUTH_USER"]) {
            $uri = $uri->withUserInfo($server["PHP_AUTH_USER"], $server["PHP_AUTH_PW"]);
        }

        if ($server["REQUEST_URI"]) {
            $path = $server["REQUEST_URI"];
            if ($server["QUERY_STRING"]) {
                $path = substr($path, 0, - (strlen($server["QUERY_STRING"]) + 1));
            }
            $uri = $uri->withPath($path);
        }

        $uri = $uri->withQuery($server["QUERY_STRING"] ?? "");

        $this->cookies = $_COOKIE;

        $this->uri = $uri;

        $this->body = new Stream(fopen("php://input", "r"));

        if ($_FILES) {

            $parseUploadedFile = function ($files) use (&$parseUploadedFile) {

                $data = [];
                foreach ($files as $name => $file) {

                    if (is_int($file["error"])) {
                        $data[$name] = new UploadedFile($file);

                        continue;
                    }

                    $child = [];

                    foreach ($file['error'] as $id => $error) {
                        $child[$id]['name'] = $file['name'][$id];
                        $child[$id]['type'] = $file['type'][$id];
                        $child[$id]['tmp_name'] = $file['tmp_name'][$id];
                        $child[$id]['error'] = $file['error'][$id];
                        $child[$id]['size'] = $file['size'][$id];
                    }
                    $data[$name] = $parseUploadedFile($child);
                }
                return $data;
            };
            $this->uploadedFiles = $parseUploadedFile($_FILES);
        }
    }

    public function getServerParams()
    {
        return $_SERVER;
    }

    public function getCookieParams()
    {
        return $this->cookies;
    }


    public function getQueryParams()
    {
        $result = [];
        parse_str($this->getUri()->getQuery(), $result);

        foreach ($this->query as $name => $query) {
            $result[$name] = $query;
        }
        return $result;
    }

    public function getParsedBody()
    {
        $body = (string)$this->getBody();
        if (strpos($this->getHeaderLine("Content-Type"), "application/json") !== false) {
            return json_decode($body, true);
        }
        $parsed_body = [];
        parse_str($body, $parsed_body);
        return $body;
    }

    public function getUploadedFiles()
    {
        return $this->uploadedFiles;
    }

    public function getAttribute($name, $default = null)
    {
        return $this->attributes[$name] ?? $default;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function withUploadedFiles(array $uploadedFiles)
    {
        $clone = clone $this;
        $clone->uploadedFiles = $uploadedFiles;
        return $clone;
    }

    public function withCookieParams(array $cookies)
    {
        $clone = clone $this;
        $clone->cookies = $cookies;
        return $clone;
    }

    public function withQueryParams(array $query)
    {
        $clone = clone $this;
        $clone->query = $query;
        return $clone;
    }

    public function withParsedBody($data)
    {
        $stream = (new Stream(serialize($data)));
        return $this->withBody($stream);
    }

    public function withAttribute($name, $value)
    {
        $clone = clone $this;
        $clone->attributes[$name] = $value;
        return $clone;
    }

    public function withoutAttribute($name)
    {
        $clone = clone $this;
        unset($clone->attributes[$name]);
        return $clone;
    }
}
