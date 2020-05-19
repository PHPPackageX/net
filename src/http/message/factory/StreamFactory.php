<?php
namespace PHPPackageX\net\http\message\factory;

use PHPPackageX\net\http\message\Stream;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

class StreamFactory implements StreamFactoryInterface
{
    /**
     * @param  string $content
     * @return StreamInterface
     */
    public function createStream(string $content = ''): StreamInterface
    {
        $resource = fopen('php:://temp', 'r+');

        if (!empty($content)) {
            fwrite($resource, $content);
            fseek($resource, 0);
        }

        return new Stream($resource);
    }

    /**
     * @param  string $filename
     * @param  string $mode
     * @return StreamInterface
     */
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        $resource = fopen($filename, $mode);

        if (!$resource) {
            throw new \RuntimeException();
        }

        return new Stream($resource);
    }

    /**
     * @param  resource $resource
     * @return StreamInterface
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        return new Stream($resource);
    }
}