<?php

namespace App\Message;

use App\Entity\Document;

class DocUploadMessage
{




   
    private $id;

    public function __construct(string $id)
    {
     
        $this->id = $id;
    }

   

    public function getId(): string
    {
        return $this->id;
    }
}
