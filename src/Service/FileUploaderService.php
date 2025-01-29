<?php

namespace App\Service;

use App\Event\AddFile;
use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use thiagoalessio\TesseractOCR\TesseractOCR;
class FileUploaderService
{

  
    
    private $directory;

    public function __construct(private SluggerInterface $slugger,ParameterBagInterface $param, private EventDispatcherInterface $dispatcher) {
        $this->directory=$param->get('docs_directory');
      
    }
    public function uploadFile(
        UploadedFile $file,
       
        string $vs
    ) {
        
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        // this is needed to safely include the file name as part of the URL
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().$vs.'.'.$file->guessExtension() ;

        // Move the file to the directory where brochures are stored
        try {
            $file->move(
                $this->directory.'original',
                $newFilename
            );


                    $path_parts = pathinfo($newFilename);
                   
                    if(in_array($path_parts['extension'],['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg','png']))
                    {
                        try {
                            $text=(new TesseractOCR ( $this->directory.'original/'.$newFilename))->run();

                        }catch(Exception $e) {
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
                    $parser= new \Smalot\PdfParser\Parser();
                    try {
                        $pdf=$parser->parseContent($content);    

                        $text=$pdf->getText();
                    }catch(Exception $e) {
                      $text='';
                    }
                  
                    }
          
           


        } catch (Exception $e) {
            // ... handle exception if something happens during file upload
            echo 'oops something went wrong 🙂😮😒';
            echo ( $e->getMessage());
        }
        return ['newFilename'=>$newFilename,'content'=>$text];
    }
}