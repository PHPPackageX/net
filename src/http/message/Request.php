<?php
namespace PHPPackageX\net\http\message;

use PHPPackageX\net\http\message\traits\MethodFilterTrait;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class Request extends Message implements RequestInterface
{
    use MethodFilterTrait;

    /**
     * @var string 请求报文方法
     */
    protected $method;

    /**
     * @var UriInterface 请求报文URI实例
     */
    protected $uri;

    /**
     * @var string
     */
    protected $requestTarget;

    /**
     * @param string          $method
     * @param UriInterface    $uri
     * @param array           $headers
     * @param StreamInterface $body
     * @param string          $version
     */
    public function __construct(string $method, UriInterface $uri, array $headers = [], StreamInterface $body = null, string $version = '1.1')
    {
        $this->method = $this->filterMethod($method);
        $this->uri    = $uri;

        parent::__construct($headers, $body, $version);

        if (!isset($this->headersName['host'])) {
            $this->updateHeaderHostByUri();
        }
    }

    /**
     * 根据指定方法，返回新的请求报文实例
     * @param  string $method
     * @return Request
     */
    public function withMethod($method)
    {
        return $this->deepCopy(['method' => $this->filterMethod($method)]);
    }

    /**
     * 获取请求报文方法
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * 根据指定URI实例，返回新的请求报文实例
     * @param  UriInterface $uri
     * @param  bool         $preserveHost
     * @return $this|static
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        if ($uri === $this->uri) {
            return $this;
        }

        $newRequest = $this->deepCopy(['uri' => $uri]);

        if (!$preserveHost || !isset($newRequest->headerNames['host'])) {
            $newRequest->updateHeaderHostByUri();
        }

        return $newRequest;
    }

    /**
     * 获取请求报文URI实例
     * @return UriInterface
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * 根据指定Target，返回新的请求报文实例
     * @param  mixed  $requestTarget
     * @return static
     */
    public function withRequestTarget($requestTarget)
    {
        if (preg_match('#\s#', $requestTarget)) {
            throw new \InvalidArgumentException(
                'Invalid request target provided; cannot contain whitespace'
            );
        }

        return $this->deepCopy(['requestTarget' => $requestTarget]);
    }

    /**
     * 获取请求报文Target
     * @return string
     */
    public function getRequestTarget()
    {
        if ($this->requestTarget !== null) {
            return $this->requestTarget;
        }

        $target = $this->uri->getPath();

        if ($target == '') {
            $target = '/';
        }

        if ($this->uri->getQuery() != '') {
            $target .= '?' . $this->uri->getQuery();
        }

        return $target;
    }

    /**
     * 根据Uri实例更新请求报文Header Host字段
     */
    private function updateHeaderHostByUri()
    {
        $host = $this->uri->getHost();

        if ($host == '') {
            return;
        }

        if (($port = $this->uri->getPort()) !== null) {
            $host .= ':' . $port;
        }

        if (isset($this->headerNames['host'])) {
            $header = $this->headersName['host'];
        } else {
            $header = 'Host';
            $this->headersName['host'] = 'Host';
        }
        // Ensure Host is the first header.
        // See: http://tools.ietf.org/html/rfc7230#section-5.4
        $this->headers = [$header => [$host]] + $this->headers;
    }
}