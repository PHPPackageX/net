<?php
namespace PHPPackageX\net\http\message\factory;

use PHPPackageX\net\http\message\server\ServerRequest;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

class ServerRequestFactory extends MessageFactory implements ServerRequestFactoryInterface
{
    /**
     * 创建服务端-请求报文实例
     * @param  string                $method
     * @param  UriInterface|string   $uri
     * @param  array                 $serverParams
     * @return ServerRequestInterface
     */
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        if (is_string($uri)) {
            $uri = (new UriFactory())->createUri($uri);
        }

        if (!$uri instanceof UriInterface) {
            return null;
        }

        return new ServerRequest($method, $uri, $this->headers, $this->body, $this->version, $serverParams);
    }
}