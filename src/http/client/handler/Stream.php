<?php
namespace PHPPackageX\net\http\client\strategy;

use PHPPackageX\net\http\client\contracts\HandlerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Stream implements HandlerInterface
{
    public function sendRequest(RequestInterface $request):ResponseInterface
    {
        // TODO: Implement sendRequest() method.
    }

    public function contextIsEnable($strict):bool
    {
        if (!ini_get('allow_url_fopen')) {
            return false;
        }

        return true;
    }
}