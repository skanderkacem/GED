<?php

namespace App\Event;


use Symfony\Contracts\EventDispatcher\Event;


class AddFile extends Event
{

    const ADD_File = 'file.add';
     public function __construct(private string $filename,private string $directory) {
}

public function getFileName()
{
    return $this->filename ;
}

public function getDirectory()
{
    return $this->directory ;
}

}