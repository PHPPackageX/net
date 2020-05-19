<?php
namespace PHPPackageX\net\http\message\factory;

use Psr\Http\Message\StreamInterface;

abstract class MessageFactory
{
    /**
     * @var string 报文请求行版本号
     */
    protected $version = '1.1';

    /**
     * @var array 报文首部
     */

    protected $headers = [];

    /**
     * @var StreamInterface 报文主体
     */
    protected $body;
}