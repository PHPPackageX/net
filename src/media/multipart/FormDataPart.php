<?php
namespace PHPPackageX\net\media\multipart;

class FormDataPart
{
    protected $name;
    protected $value;
    protected $disposition;
    protected $charset;
    protected $fileName;
    protected $contentType;
    protected $transferEncoding;

    public function __construct(
        string $name,
        string $value,
        string $fileName         ='',
        string $contentType      = '',
        string $disposition      = 'form-data',
        string $transferEncoding = '',
        ?string $charset         = null)
    {
        $this->name             = $name;
        $this->value            = $value;
        $this->disposition      = $disposition;
        $this->charset          = $charset;
        $this->fileName         = $fileName;
        $this->contentType      = $contentType;
        $this->transferEncoding = $transferEncoding;
    }

    public function __toString()
    {
        $out = '';
        if ($this->disposition) {
            $out .= 'Content-Disposition: ' . $this->disposition;
            if ($this->name) {
                $out .= '; ' . sprintf('%s="%s"', 'name', $this->name);
            }
            if ($this->fileName) {
                $out .= '; ' . sprintf('%s="%s"', 'filename', $this->fileName);
            }
            $out .= "\r\n";
        }
        if ($this->contentType) {
            $out .= 'Content-Type: ' . $this->contentType . "\r\n";
        }
        if ($this->transferEncoding) {
            $out .= 'Content-Transfer-Encoding: ' . $this->transferEncoding . "\r\n";
        }

        $out .= "\r\n";
        $out .= $this->value;

        return $out;
    }
}