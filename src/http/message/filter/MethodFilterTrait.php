<?php
namespace PHPPackageX\net\http\message\traits;

trait MethodFilterTrait
{
    protected function filterMethod($method)
    {
        if (!is_string($method) || $method === '') {
            throw new \InvalidArgumentException('Method must be a non-empty string.');
        }

        return strtoupper($method);
    }
}
