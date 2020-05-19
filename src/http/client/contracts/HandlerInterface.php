<?php
namespace PHPPackageX\net\http\client\contracts;

use Psr\Http\Message\RequestInterface;

interface HandlerInterface
{
    public function sendRequest(RequestInterface $request);
    public function contextIsEnable(bool $strict);
}
