<?php
namespace PHPPackageX\net\media;

interface MediaInterface
{
    public function getContentType();
    public function setContent($content);
    public function setCharset($charset);
    public function __toString();
}