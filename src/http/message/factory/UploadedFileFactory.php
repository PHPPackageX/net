<?php
namespace PHPPackageX\net\http\message\factory;

use PHPPackageX\net\http\message\server\UploadedFile;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;

class UploadedFileFactory implements UploadedFileFactoryInterface
{
    /**
     * 创建服务端-上传文件实例
     * @param  StreamInterface $stream
     * @param  int|null        $size
     * @param  int             $error
     * @param  string|null     $clientFilename
     * @param  string|null     $clientMediaType
     * @return UploadedFileInterface
     */
    public function createUploadedFile(
        StreamInterface $stream,
        int $size = null,
        int $error = \UPLOAD_ERR_OK,
        string $clientFilename = null,
        string $clientMediaType = null
    ): UploadedFileInterface
    {
        return new UploadedFile($stream, $size, $error, $clientFilename, $clientMediaType);
    }
}