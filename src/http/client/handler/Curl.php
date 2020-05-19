<?php
namespace PHPPackageX\net\http\client\strategy;

use PHPPackageX\net\http\client\contracts\HandlerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Curl implements HandlerInterface
{
    public function sendRequest(RequestInterface $request):ResponseInterface
    {
        // TODO: Implement sendRequest() method.
    }

    public function contextIsEnable($strict)
    {
        if (!extension_loaded('curl')) {
            return false;
        }

        return true;
    }
}