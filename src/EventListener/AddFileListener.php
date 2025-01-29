<?php

namespace App\EventListener;

use App\Event\AddFile;
use App\Service\ConverterToPDFService;
use App\Service\FileEncryptor;
use Symfony\Component\HttpKernel\Event\KernelEvent;

class AddFileListener
{
  
    public function onFileAdd(AddFile $file){
        $converter= new ConverterToPDFService();
        $converter->convert( $file->getFileName(), $file->getDirectory());

        $fileEncryptor = new FileEncryptor($file->getFileName());
        $fileEncryptor->encryptFile( $file->getDirectory().'original\\'.$file->getFileName());
        $fileEncryptor->encryptFile( $file->getDirectory().'converted\\'.pathinfo($file->getFileName())['filename'].'.pdf');
    }
    
}