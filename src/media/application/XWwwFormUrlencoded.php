<?php
namespace PHPPackageX\net\media\application;

use PHPPackageX\net\media\MediaInterface;

class XWwwFormUrlencoded implements MediaInterface
{
    protected $content;

    public function getType()
    {
        return 'application/x-www-form-urlencoded';
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function __toString()
    {
        if (is_array($this->content)) {
            return http_build_query($this->content, null, '&', PHP_QUERY_RFC3986);
        }

        return $this->content;
    }
}