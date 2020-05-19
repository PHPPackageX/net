<?php
namespace PHPPackageX\net\http\message;

use Psr\Http\Message\StreamInterface;

class Stream implements StreamInterface
{
    const READABLE_MODES = '/r|a\+|ab\+|w\+|wb\+|x\+|xb\+|c\+|cb\+/';
    const WRITABLE_MODES = '/a|w|r\+|rb\+|rw|x|c/';

    private $stream;
    private $size;
    private $seekable;
    private $readable;
    private $writable;
    private $uri;
    private $metadata;

    /**
     * @param resource $stream
     * @param array    $options
     * @link  https://www.php.net/manual/zh/function.is-resource
     */
    public function __construct($stream)
    {
        if (!is_resource($stream)) {
            throw new \InvalidArgumentException('Stream must be a resource');
        }

        $this->stream   = $stream;
        $this->metadata = stream_get_meta_data($this->stream);
        $this->uri      = $this->metadata['uri'];
        $this->seekable = $this->metadata['seekable'];
        $this->readable = (bool)preg_match(self::READABLE_MODES, $this->metadata['mode']);
        $this->writable = (bool)preg_match(self::WRITABLE_MODES, $this->metadata['mode']);
    }

    /**
     * 返回流的元数据
     * @param  null $key
     * @return array|mixed|null
     * @link https://www.php.net/manual/zh/function.stream-get-meta-data.php
     */
    public function getMetadata($key = null)
    {
        if (!isset($this->stream)) {
            return $key ? null : [];
        }

        if (!$key) {
            $this->metadata = stream_get_meta_data($this->stream);
            return $this->metadata;
        }

        $this->metadata = stream_get_meta_data($this->stream);

        return isset($this->metadata[$key]) ? $this->metadata[$key] : null;
    }

    /**
     * 返回流的字节数
     * @return int|null
     * @link https://www.php.net/manual/zh/function.clearstatcache
     * @link https://www.php.net/manual/zh/function.fstat.php
     */
    public function getSize()
    {
        if ($this->size !== null) {
            return $this->size;
        }

        if (!isset($this->stream)) {
            return null;
        }

        if ($this->uri) {
            clearstatcache(true, $this->uri);
        }

        $stats = fstat($this->stream);

        if (isset($stats['size'])) {
            $this->size = $stats['size'];
            return $this->size;
        }

        return null;
    }

    /**
     * 返回剩余内容的字符串格式
     * @return bool|string
     * @link https://www.php.net/manual/zh/function.stream-get-contents
     */
    public function getContents()
    {
        if (!isset($this->stream)) {
            throw new \RuntimeException('Stream is detached');
        }

        $contents = stream_get_contents($this->stream);

        if ($contents === false) {
            throw new \RuntimeException('Unable to read stream contents');
        }

        return $contents;
    }

    /**
     * 如果到达流的末尾返回true
     * @return bool
     * @link https://www.php.net/manual/zh/function.feof.php
     */
    public function eof()
    {
        if (!isset($this->stream)) {
            throw new \RuntimeException('Stream is detached');
        }

        return feof($this->stream);
    }

    /**
     * 是否可寻址
     * @return bool
     */
    public function isSeekable()
    {
        return $this->seekable;
    }

    /**
     * 指针定位到指定位置
     * @param int $offset
     * @param int $whence
     * @link https://www.php.net/manual/zh/function.fseek.php
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        $whence = (int) $whence;

        if (!isset($this->stream)) {
            throw new \RuntimeException('Stream is detached');
        }
        if (!$this->seekable) {
            throw new \RuntimeException('Stream is not seekable');
        }
        if (fseek($this->stream, $offset, $whence) === -1) {
            throw new \RuntimeException('Unable to seek to stream position '
                . $offset . ' with whence ' . var_export($whence, true));
        }
    }

    /**
     * 指针定定位到开始位置
     */
    public function rewind()
    {
        $this->seek(0);
    }

    /**
     * 指针当前位置
     * @return bool|int
     * @link https://www.php.net/manual/zh/function.ftell
     */
    public function tell()
    {
        if (!isset($this->stream)) {
            throw new \RuntimeException('Stream is detached');
        }

        $result = ftell($this->stream);

        if ($result === false) {
            throw new \RuntimeException('Unable to determine stream position');
        }

        return $result;
    }

    /**
     * 是否可读
     * @return bool
     */
    public function isReadable()
    {
        return $this->readable;
    }

    /**
     * 读取流数据
     * @param  int $length
     * @return bool|string
     * @link https://www.php.net/manual/zh/function.fread.php
     */
    public function read($length)
    {
        if (!isset($this->stream)) {
            throw new \RuntimeException('Stream is detached');
        }
        if (!$this->readable) {
            throw new \RuntimeException('Cannot read from non-readable stream');
        }
        if ($length < 0) {
            throw new \RuntimeException('Length parameter cannot be negative');
        }

        if (0 === $length) {
            return '';
        }

        $string = fread($this->stream, $length);

        if (false === $string) {
            throw new \RuntimeException('Unable to read from stream');
        }

        return $string;
    }

    /**
     * 是否可写
     * @return bool
     */
    public function isWritable()
    {
        return $this->writable;
    }

    /**
     * 写入流数据
     * @param  string $string
     * @return bool|int
     * @link   https://www.php.net/manual/zh/function.fwrite
     */
    public function write($string)
    {
        if (!isset($this->stream)) {
            throw new \RuntimeException('Stream is detached');
        }
        if (!$this->writable) {
            throw new \RuntimeException('Cannot write to a non-writable stream');
        }

        $this->size = null;
        $result = fwrite($this->stream, $string);

        if ($result === false) {
            throw new \RuntimeException('Unable to write to stream');
        }

        return $result;
    }

    /**
     * 流与资源分离
     * @return null|resource
     */
    public function detach()
    {
        if (!isset($this->stream)) {
            return null;
        }

        $result = $this->stream;
        unset($this->stream);
        $this->size = $this->uri = null;
        $this->readable = $this->writable = $this->seekable = false;

        return $result;
    }

    /**
     * 关闭资源
     * @link https://www.php.net/manual/zh/function.fclose.php
     */
    public function close()
    {
        if (isset($this->stream)) {
            if (is_resource($this->stream)) {
                fclose($this->stream);
            }
            $this->detach();
        }
    }

    public function __destruct()
    {
        $this->close();
    }

    public function __toString()
    {
        try {
            $this->seek(0);
            return (string) stream_get_contents($this->stream);
        } catch (\Exception $e) {
            return '';
        }
    }
}