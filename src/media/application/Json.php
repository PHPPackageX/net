<?php
namespace PHPPackageX\net\media\application;

use PHPPackageX\net\media\MediaInterface;

class Json implements MediaInterface
{
    protected $content;

    public function getType()
    {
        return 'application/json';
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function __toString()
    {
        if (is_array($this->content)) {
            return json_encode($this->content);
        }

        return $this->content;
    }
}