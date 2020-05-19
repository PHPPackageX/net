<?php
namespace PHPPackageX\net\http\message\factory;

use PHPPackageX\net\http\message\Response;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

class ResponseFactory implements ResponseFactoryInterface
{
    /**
     * 创建响应报文实例
     * @param  int     $code
     * @param  string  $reasonPhrase
     * @return ResponseInterface
     */
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        $response = new Response($code, [], null, $reasonPhrase);

        return $response;
    }
}