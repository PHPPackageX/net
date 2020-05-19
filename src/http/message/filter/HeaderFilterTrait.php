<?php
namespace PHPPackageX\net\http\message\traits;

trait HeaderFilterTrait
{
    protected function filterHeaderName($name)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException(sprintf(
                'Header name must be a string but %s provided.',
                is_object($name) ? get_class($name) : gettype($name)
            ));
        }

        if ($name === '') {
            throw new \InvalidArgumentException('Header name can not be empty.');
        }

        return strtolower($name);
    }

    protected function filterHeaderValues($values)
    {
        if (!is_array($values)) {
            $values = [$values];
        }

        if (count($values) === 0) {
            throw new \InvalidArgumentException('Header value can not be an empty array.');
        }

        return array_map(function ($value) {
            if (!is_scalar($value) && null !== $value) {
                throw new \InvalidArgumentException(sprintf(
                    'Header value must be scalar or null but %s provided.',
                    is_object($value) ? get_class($value) : gettype($value)
                ));
            }

            return trim((string) $value, " \t");
        }, $values);
    }
}
