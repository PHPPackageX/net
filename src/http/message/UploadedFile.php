<?php
namespace PHPPackageX\net\http\message\server;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

class UploadedFile implements UploadedFileInterface
{
    private $moved = false;
    private $file;

    private $clientFilename;
    private $clientMediaType;
    private $error;
    private $size;

    /**
     * @var StreamInterface
     */
    private $stream;

    public function __construct(
        StreamInterface $stream,
        int $size = null,
        int $error = \UPLOAD_ERR_OK,
        string $clientFilename  = null,
        string $clientMediaType = null)
    {
        if (\UPLOAD_ERR_OK == $error) {
            $this->stream = $stream;
        }

        $this->size            = $size;
        $this->error           = $error;
        $this->clientFilename  = $clientFilename;
        $this->clientMediaType = $clientMediaType;
    }

    /**
     * @return int|null
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return null|string
     */
    public function getClientFilename()
    {
        return $this->clientFilename;
    }

    /**
     * @return null|string
     */
    public function getClientMediaType()
    {
        return $this->clientMediaType;
    }

    /**
     * @return int
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return StreamInterface
     */
    public function getStream()
    {
        $this->validateActive();

        if ($this->stream instanceof StreamInterface) {
            return $this->stream;
        }

        //return new LazyOpenStream($this->file, 'r+');
    }

    /**
     * @param string $targetPath
     */
    public function moveTo($targetPath)
    {
        $this->validateActive();

        if (false === $this->isStringNotEmpty($targetPath)) {
            throw new \InvalidArgumentException(
                'Invalid path provided for move operation; must be a non-empty string'
            );
        }

        if ($this->file) {
            $this->moved = php_sapi_name() == 'cli'
                ? rename($this->file, $targetPath)
                : move_uploaded_file($this->file, $targetPath);
        } else {
            /*
            copy_to_stream(
                $this->getStream(),
                new LazyOpenStream($targetPath, 'w')
            );
            */

            $this->moved = true;
        }

        if (false === $this->moved) {
            throw new \RuntimeException(
                sprintf('Uploaded file could not be moved to %s', $targetPath)
            );
        }
    }

    /* ------------------ 工具 -------------------- */

    public function isMoved()
    {
        return $this->moved;
    }

    private function validateActive()
    {
        if (false === $this->isOk()) {
            throw new \RuntimeException('Cannot retrieve stream due to upload error');
        }

        if ($this->isMoved()) {
            throw new \RuntimeException('Cannot retrieve stream after it has already been moved');
        }
    }

    private function isOk()
    {
        return $this->error === UPLOAD_ERR_OK;
    }

    private function isStringNotEmpty($param)
    {
        return is_string($param) && false === empty($param);
    }
}