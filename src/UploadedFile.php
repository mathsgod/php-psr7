<?php

namespace PHP\Psr7;

use Psr\Http\Message\UploadedFileInterface;

class UploadedFile implements UploadedFileInterface
{

    protected $name;
    protected $size;
    protected $type;
    protected $stream;
    protected $error;

    public function __construct(array $file)
    {
        if ($file["tmp_name"]) {
            $this->stream = new Stream(fopen($file["tmp_name"], "r"));
        }

        $this->size = $file["size"];
        $this->error = $file["error"];
        $this->name = $file["name"];
        $this->type = $file["type"];
    }

    public function getStream()
    {
        return $this->stream;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function moveTo($targetPath)
    {
        move_uploaded_file($this->stream->getMetadata("uri"), $targetPath);
    }


    public function getClientFilename()
    {
        return $this->name;
    }

    public function getClientMediaType()
    {
        return $this->type;
    }

    public function getError()
    {
        return $this->error;
    }
}
