<?php
namespace PHPPackageX\net\http\message\traits;

trait UriFilterTrait
{
    //未保留字符
    private static $charUnreserved = 'a-zA-Z0-9_\-\.~';
    //保留字符-分割每个组件的
    private static $charComponentReserved = '!\$&\'\(\)\*\+,;=';


    protected function filterScheme($scheme)
    {
        if (!is_string($scheme)) {
            throw new \InvalidArgumentException('Scheme must be a string');
        }

        return strtolower($scheme);
    }

    protected function filterUserInfoComponent($component)
    {
        if (!is_string($component)) {
            throw new \InvalidArgumentException('User info must be a string');
        }

        return preg_replace_callback(
            '/(?:[^%' . self::$charUnreserved . self::$charComponentReserved . ']+|%(?![A-Fa-f0-9]{2}))/',
            [$this, 'rawUrlEncodeMatchZero'],
            $component
        );
    }

    protected function filterHost($host)
    {
        if (!is_string($host)) {
            throw new \InvalidArgumentException('Host must be a string');
        }

        return strtolower($host);
    }

    protected function filterPort($port)
    {
        if ($port === null) {
            return null;
        }

        $port = (int) $port;
        if (0 > $port || 0xffff < $port) {
            throw new \InvalidArgumentException(
                sprintf('Invalid port: %d. Must be between 0 and 65535', $port)
            );
        }

        return $port;
    }

    protected function filterQueryAndFragment($str)
    {
        if (!is_string($str)) {
            throw new \InvalidArgumentException('Query and fragment must be a string');
        }

        return preg_replace_callback(
            '/(?:[^' . self::$charUnreserved . self::$charComponentReserved . '%:@\/\?]++|%(?![A-Fa-f0-9]{2}))/',
            [$this, 'rawUrlEncodeMatchZero'],
            $str
        );
    }

    protected function filterPath($path)
    {
        if (!is_string($path)) {
            throw new \InvalidArgumentException('Path must be a string');
        }

        return preg_replace_callback(
            '/(?:[^' . self::$charUnreserved . self::$charComponentReserved . '%:@\/]++|%(?![A-Fa-f0-9]{2}))/',
            [$this, 'rawUrlEncodeMatchZero'],
            $path
        );
    }

    private function rawUrlEncodeMatchZero(array $match)
    {
        return rawurlencode($match[0]);
    }
}
