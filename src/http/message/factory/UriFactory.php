<?php
namespace PHPPackageX\net\http\message\factory;

use PHPPackageX\net\http\message\Uri;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

class UriFactory implements UriFactoryInterface
{
    /**
     * 创建URI实例
     * @param  string $uri
     * @return UriInterface
     */
    public function createUri(string $uri = ''): UriInterface
    {
        return new Uri($uri);
    }
}