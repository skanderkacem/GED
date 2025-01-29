<?php

namespace App\Message;

class PdfConversionMessage
{
    private $filename;
    private $inputDirectory;

    public function __construct(string $filename, string $inputDirectory)
    {
        $this->filename = $filename;
        $this->inputDirectory = $inputDirectory;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getInputDirectory(): string
    {
        return $this->inputDirectory;
    }
}
