<?php
namespace PHPPackageX\net\media\multipart;

use PHPPackageX\net\media\MediaInterface;
use \finfo;

class FormData implements MediaInterface
{
    protected $boundary;
    protected $parts;

    public function getBoundary()
    {
        if ($this->boundary) {
            return $this->boundary;
        }
        $this->boundary = md5(uniqid((string)time()));

        return $this->boundary;
    }

    public function getContentType()
    {
        return 'multipart/form-data; boundary='.$this->getBoundary();
    }

    public function setContent($content)
    {
        foreach($content as $name => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $k = $name . '[' . $k . ']';
                    $newContent[$k] = $v;
                }
                $this->setContent($newContent);
            } elseif (is_resource($value)) {
                $filename = '';
                $contentType = 'application/octet-stream';
                $content = stream_get_contents($value);
                if (stream_is_local($value)) {
                    $metadata    = stream_get_meta_data($value);
                    $contentType = (new finfo(FILEINFO_MIME))->file($metadata['uri']);
                    $filename    = basename($metadata['uri']);
                }
                $this->parts[] = new FormDataPart($name, $content, $filename, $contentType);;
            } else {
                $this->parts[] = new FormDataPart($name, (string)$value);
            }
        }
    }

    public function __toString()
    {
        $boundary = $this->getBoundary();
        $out = '';
        foreach ($this->parts as $part) {
            $out .= "--$boundary\r\n";
            $out .= (string)$part;
            $out .= "\r\n";
        }
        $out .= "--$boundary--\r\n";

        return $out;
    }

    public function setCharset($charset = '')
    {

    }
}