<?php
namespace PHPPackageX\net\http\message;

use PHPPackageX\copy\traits\DeepCopyTrait;
use PHPPackageX\net\http\message\traits\HeaderFilterTrait;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

abstract class Message implements MessageInterface
{
    use DeepCopyTrait;

    use HeaderFilterTrait;

    /**
     * @var StreamInterface
     */
    protected $body;
    /**
     * @var array
     */
    protected $headers         = [];
    /**
     * @var array
     */
    protected $headersName     = [];
    /**
     * @var string
     */
    protected $protocolVersion = '1.1';

    /**
     * @param array           $headers
     * @param StreamInterface $body
     * @param string          $version
     */
    public function __construct(array $headers, StreamInterface $body, $version = '1.1')
    {
        $this->protocolVersion = $version;

        foreach ($headers as $name => $value) {
            if (is_int($name)) {
                $name = (string) $name;
            }

            $formatName = $this->filterHeaderName($name);

            if (isset($this->headersName[$formatName])) {
                $name = $this->headersName[$formatName];
            } else {
                $this->headersName[$formatName] = $name;
            }

            $this->headers[$name] = array_merge($this->getHeader($name), $this->filterHeaderValues($value));
        }

        $this->body = $body;
    }

    /**
     * 获取报文HTTP协议版本号
     * @return string
     */
    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    /**
     * 根据指定HTTP版本号返回新的报文实例
     * @param  string $version
     * @return $this|Message|static
     */
    public function withProtocolVersion($version)
    {
        if ($this->protocolVersion == $version) {
            return $this;
        }

        return $this->deepCopy(['protocolVersion' => $version]);
    }

    /**
     * 判断报文是否有指定首部字段
     * @param  string $name
     * @return bool
     */
    public function hasHeader($name)
    {
        return isset($this->headersName[$this->filterHeaderName($name)]);
    }

    /**
     * 根据指定首部字段返回新的报文实例
     * @param  string $name
     * @param  string|string[] $value
     * @return Message|static
     */
    public function withHeader($name, $value)
    {
        $headers = $this->headers;
        $headers[$name] = $this->filterHeaderValues($value);

        $headersName = $this->headersName;
        $headersName[$this->filterHeaderName($name)] = $name;

        return $this->deepCopy([
            'headers'     => $headers,
            'headersName' => $headersName
        ]);
    }

    /**
     * 给已存在的首部字段添加新值，并返回新的报文实例
     * @param  string $name
     * @param  string|string[] $value
     * @return Message|static
     */
    public function withAddedHeader($name, $value)
    {
        $headers = $this->headers;
        $headers[$name] =  array_merge($this->getHeader($name), $this->filterHeaderValues($value));
        $headersName = $this->headersName;
        $headersName[$this->filterHeaderName($name)] = $name;

        return $this->deepCopy([
            'headers'     => $headers,
            'headersName' => $headersName
        ]);
    }

    /**
     * 删除指定首部字段，并返回新的报文实例
     * @param  string $name
     * @return Message|static
     */
    public function withoutHeader($name)
    {
        $formatName = $this->filterHeaderName($name);

        if (!isset($this->headersValue[$formatName])) {
            return $this;
        }

        $headers     = $this->headers;
        $headersName = $this->headersName;

        unset($headers[$name], $headersName[$formatName]);

        return $this->deepCopy([
            'headers'     => $headers,
            'headersName' => $headersName
        ]);
    }

    /**
     * 获取报文首部指定字段
     * @param  string $name
     * @return array|mixed|string[]
     */
    public function getHeader($name)
    {
        $name = $this->filterHeaderName($name);

        if (isset($this->headersName[$name])){
            return [];
        }

        return $this->headers[$this->headersName[$name]];
    }

    /**
     * 获取报文首部指定字段字符串格式
     * @param  string $name
     * @return string
     */
    public function getHeaderLine($name)
    {
        return implode(', ', $this->getHeader($name));
    }

    /**
     * 获取报文首部所有字段
     * @return array|\string[][]
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * 获取报文主体流对象
     * @return null|StreamInterface
     */
    public function getBody()
    {
        if (!$this->body) {
            $this->body = null;
        }

        return $this->body;
    }

    /**
     * 根据指定主体流对象，返回新的报文实例
     * @param  StreamInterface $body
     * @return $this|Message|static
     */
    public function withBody(StreamInterface $body)
    {
        if ($body === $this->body) {
            return $this;
        }

        return $this->deepCopy(['body' => $body]);
    }
}