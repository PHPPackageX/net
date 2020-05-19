<?php
namespace PHPPackageX\net\http\message\traits;

trait StatusCodeFilterTrait
{
    protected function filterStatusCode($statusCode)
    {
        if (filter_var($statusCode, FILTER_VALIDATE_INT) === false) {
            throw new \InvalidArgumentException('Status code must be an integer value.');
        }

        if ($statusCode < 100 || $statusCode >= 600) {
            throw new \InvalidArgumentException('Status code must be an integer value between 1xx and 5xx.');
        }

        return $statusCode;
    }
}
