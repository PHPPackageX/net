<?php
namespace PHPPackageX\net\http\message;

use PHPPackageX\net\http\message\traits\StatusCodeFilterTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class Response extends Message implements ResponseInterface
{
    use StatusCodeFilterTrait;

    /**
     * @var int 响应报文状态码
     */
    protected $statusCode   = 200;

    /**
     * @var string 响应报文原因短语
     */
    protected $reasonPhrase = '';

    /**
     * @param int             $code
     * @param array           $headers
     * @param StreamInterface $body
     * @param string          $reason
     * @param string          $version
     */
    public function __construct(int $code, array $headers = [], StreamInterface $body = null, string $reason = '', string $version = '1.1')
    {
        $this->statusCode   = $this->filterStatusCode($code);
        $this->reasonPhrase = $reason;

        parent::__construct($headers, $body, $version);
    }

    /**
     * 获取响应报文状态码
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * 获取响应报文原因短语
     * @return string
     */
    public function getReasonPhrase()
    {
        return $this->reasonPhrase;
    }

    /**
     * 根据指定状态码，返回新的响应报文实例
     * @param  int    $code
     * @param  string $reasonPhrase
     * @return static
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        return $this->deepCopy([
            'statusCode' => $this->filterStatusCode($code),
            'reasonPhrase' => $reasonPhrase
        ]);
    }
}