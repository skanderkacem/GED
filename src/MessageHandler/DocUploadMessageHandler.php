<?php


// src/MessageHandler/FileUploadMessageHandler.php
namespace App\MessageHandler;

use App\Entity\Document;
use App\Entity\DocVersion;
use App\Message\DocUploadMessage;
use App\Message\FileUploadMessage;
use App\Service\FileUploaderServiceclassify2;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;


#[AsMessageHandler]
class DocUploadMessageHandler 
{
    private $entityManager;
    private $uploaderService;

    public function __construct(EntityManagerInterface $entityManager, FileUploaderServiceclassify2 $uploaderService)
    {
        $this->entityManager = $entityManager;
        $this->uploaderService = $uploaderService;
    }

    public function __invoke(DocUploadMessage $message)
    {
        $id=$message->getId();
        $doc=$this->entityManager->getRepository(Document::class)->findOneBy(['id'=>$id]);
       
       

        if ($doc) {
            $uploadResult = $this->uploaderService->uploadFile($doc->getStorageName()
        );
    
        $version=new DocVersion();
        $doc->addDocVersion($version);
        $doc->setCurrentVersion(1);
        $version->setnumber(1);
        $version->setFileName( $doc->getStorageName());
        $doc->setTextContent($uploadResult['content']);


        $this->entityManager->persist($doc);
        $this->entityManager->flush();

        }
    }
}
