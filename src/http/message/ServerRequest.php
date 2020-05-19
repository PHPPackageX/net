<?php
namespace PHPPackageX\net\http\message;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class ServerRequest extends Request implements ServerRequestInterface
{
    private $parsedBody;
    private $attributes    = [];
    private $cookieParams  = [];
    private $queryParams   = [];
    private $serverParams  = [];
    private $uploadedFiles = [];

    /**
     * @param string          $method
     * @param UriInterface    $uri
     * @param array           $headers
     * @param StreamInterface $body
     * @param string $version
     */
    public function __construct(
        string          $method,
        UriInterface    $uri,
        array           $headers,
        StreamInterface $body,
        string $version = '1.1',
        array  $serverParams = [])
    {
        $this->serverParams = $serverParams;

        parent::__construct($method, $uri, $headers, $body, $version);
    }

    /**
     * @param  array $cookies
     * @return static
     */
    public function withCookieParams(array $cookies)
    {
        return $this->deepCopy(['cookieParams' => $cookies]);
    }

    /**
     * @return array
     */
    public function getCookieParams()
    {
        return $this->cookieParams;
    }

    /**
     * @param  array  $query
     * @return static
     */
    public function withQueryParams(array $query)
    {
        return $this->deepCopy(['queryParams' => $query]);
    }

    /**
     * @return array
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param  string $name
     * @param  mixed  $value
     * @return static
     */
    public function withAttribute($name, $value)
    {
        $attributes = $this->attributes;
        $attributes[$name] = $value;

        return $this->deepCopy(['attributes' => $attributes]);
    }

    /**
     * @param  string $name
     * @return $this|static
     */
    public function withoutAttribute($name)
    {
        $attributes = $this->attributes;

        if (!isset($this->attributes[$name])) {
            return $this;
        }

        unset($attributes[$name]);

        return $this->deepCopy(['attributes' => $attributes]);
    }

    /**
     * @param  string $name
     * @param  null   $default
     * @return mixed|null
     */
    public function getAttribute($name, $default = null)
    {
        if (!isset($this->attributes[$name])) {
            return $default;
        }

        return $this->attributes[$name];
    }

    /**
     * @param  array  $uploadedFiles
     * @return static
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        return $this->deepCopy(['uploadedFiles' => $uploadedFiles]);
    }

    /**
     * @return array
     */
    public function getUploadedFiles()
    {
        return $this->uploadedFiles;
    }

    /**
     * @param  array|null|object $data
     * @return static
     */
    public function withParsedBody($data)
    {
        return $this->deepCopy(['parsedBody' => $data]);
    }

    /**
     * @return array|null|object
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * @return array
     */
    public function getServerParams()
    {
        return $this->serverParams;
    }
}