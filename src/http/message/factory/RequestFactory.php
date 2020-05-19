<?php
namespace PHPPackageX\net\http\message\factory;

use PHPPackageX\net\http\message\Request;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\RequestFactoryInterface;

class RequestFactory implements RequestFactoryInterface
{
    /**
     * 创建请求报文实例
     * @param  string                 $method
     * @param  string|streamInterface $uri
     * @return RequestInterface
     */
    public function createRequest(string $method = '', $uri = ''): RequestInterface
    {
        if (is_string($uri)) {
            $uri = (new UriFactory())->createUri($uri);
        }

        if (!$uri instanceof UriInterface) {
            return null;
        }

        $request =  new Request($method, $uri);

        return $request;
    }
}