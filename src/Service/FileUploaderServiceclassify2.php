<?php

namespace App\Service;

use App\Event\AddFile;
use FFI\Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use thiagoalessio\TesseractOCR\TesseractOCR;

class FileUploaderServiceclassify2
{  
    
    private $directory;
    private $parser ;

    
    public function __construct(private SluggerInterface $slugger,ParameterBagInterface $param, private EventDispatcherInterface $dispatcher) {
        $this->directory=$param->get('docs_directory');
        $this->parser= new \Smalot\PdfParser\Parser();
      
    }
    public function uploadFile(
       string $newFilename 
    ) {
        

        try {
          
                  

            $path_parts = pathinfo($newFilename);
                   
            if(in_array($path_parts['extension'],['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg','png']))
            {
                try{
                    $text=(new TesseractOCR ( $this->directory.'original/'.$newFilename))->run();
                }catch (Exception $e) {
                    $text='';
                }
             
             $addFileEvent = new AddFile( $newFilename,$this->directory);

             $this->dispatcher->dispatch($addFileEvent,AddFile::ADD_File);
            
            }else{

            $addFileEvent = new AddFile( $newFilename,$this->directory);

            $this->dispatcher->dispatch($addFileEvent,AddFile::ADD_File);
            $filename= $path_parts['filename'].'.pdf';  
            $fileEncryptor=new FileEncryptor($newFilename);
            $content =  $fileEncryptor->decryptFile( $this->directory.'converted/'.$filename);
           
            $pdf=$this->parser->parseContent($content);    

            $text=$pdf->getText();
            }
          
           


        } catch (Exception $e) {
            echo 'it seems like some thing went wrong 😒😢' ;
        }
        return ['newFilename'=>$newFilename,'content'=>$text];
    }
}