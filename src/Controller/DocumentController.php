<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\DocVersion;
use App\Entity\Folder;
use App\Entity\Notification;
use App\Form\DocType;
use App\Service\FileEncryptor;
use App\Service\FileUploaderService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\File;

#[Route('/doc'),IsGranted('IS_AUTHENTICATED_FULLY')]
class DocumentController extends AbstractController
{
 


    #[Route('/add/{folder}', name: 'addDoc')]
    public function addDoc(ManagerRegistry $doctrine,Request $request,Security $security,$folder, FileUploaderService  $uploaderService):Response
    {   
        $doc=new Document(); 
      
        $form=$this->createForm(DocType::class,$doc,['action'=>$this->generateUrl('addDoc',['folder'=>$folder])]);
        $form->remove('folder');
        $form->remove('submitedit');

        $form->handleRequest($request);
        

        if($form->isSubmitted()&& $form->isValid())
        {   
            $doc->setCreatedBy($security->getUser());
            $entityManager=$doctrine->getManager();
            $repository=$doctrine->getRepository(Folder::class);
            $parent=$repository->findOneBy(['id'=>$folder]);
            $doc->setfolder($parent);
            

            $file = $form->get('file')->getData();
            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            
     
            if ($file) {
                $uploadResult=$uploaderService->uploadFile($file,'v'.$doc->getCurrentVersion() );
                $doc->setStorageName($uploadResult['newFilename']);
                $version=new DocVersion();
                $doc->addDocVersion($version);
                $doc->setCurrentVersion(1);
                $version->setnumber(1);
                $version->setFileName( $doc->getStorageName());
                $doc->setTextContent($uploadResult['content']);
    
            }
            $entityManager->persist($doc);

            $notif=new Notification();
            $notif->setUser($security->getUser());
            $notif->setDocument($doc);
            $notif->setAction('added a new  document (📄'.$doc->getName().') in (📁'.$doc->getFolder()->getName().')');
            $entityManager->persist($notif);

            $entityManager->flush();
          
            $this->addFlash('success',"The Document has been added  successfully  ");
           
             return $this->redirectToRoute('folder.treeView',[  'selectedFolderId'=>$folder]);
         

        }else{
            return $this->render('document/addDoc.html.twig',['form'=>$form->createView(),'id'=>$folder]);
            
        }}

        #[Route('/edit/{id?0<\d+>}', name: 'Doc.edit')]
        public function editDoc(ManagerRegistry $doctrine , Document $doc=null , Request $request , EntityManagerInterface $entityManager, FileUploaderService  $uploaderService,Security $security): Response
        {   
          
            $form=$this->createForm(DocType::class,$doc,['action'=>$this->generateUrl('Doc.edit',['id'=>$doc->getId()])]);
            
           
            //$form->remove('folder');
            $form->remove('submitadd');
            $form->remove('folder');
            $form->remove('file');
            $form->add('file', FileType::class, [
                'label' => 'upload a new version',
                // unmapped means that this field is not associated to any entity property
                'mapped' => false,
                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,
                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '102400K',
                    ])
                ],
            ]);
            
            $form->handleRequest($request);
            
            if($form->isSubmitted() && $form->isValid())
            {   
               
                $message=" has been updated  successfully";
                
                $file = $form->get('file')->getData();
             
         
                if ($file) {
                    $version=new DocVersion();
                    $doc->addDocVersion($version);
                    $version->setnumber($doc->getCurrentVersion()+1);
                   
                    $doc->setCurrentVersion($doc->getCurrentVersion()+1);
                   $uploadResult=$uploaderService->uploadFile($file,'v'.$doc->getCurrentVersion() );
                    $doc->setStorageName($uploadResult['newFilename']);

                    $version->setFileName($doc->getStorageName());

                    $doc->setTextContent($uploadResult['content']);
        
                }
    
                $entityManager=$doctrine->getManager();

                $entityManager->persist($doc);
                $notif=new Notification();
                $notif->setUser($security->getUser());
                $notif->setDocument($doc);
                $notif->setAction('updated the document (📄'.$doc->getName().')');
                $entityManager->persist($notif);
                $entityManager->flush();
    
                $name=$doc->getName();
                $this->addFlash('success',"$name $message");
    
    
               
                return $this->redirectToRoute('doc.display',['id'=>$doc->getId(),'vs'=>$doc->getCurrentVersion()]);
               
                
            }
            return $this->render('document/editDoc.html.twig',['form'=>$form->createView(),'id'=>$doc->getId()]);
        }

        #[Route('/delete/{id<\d+>}', name: 'Doc.delete')]
        public function deleteTotrash(ManagerRegistry $doctrine ,Document $doc=null):RedirectResponse
        {
    
   
           if($doc)
           {
            
               $manager=$doctrine->getManager();
               $doc->setIsDeleted(true);
               $doc->setIsAff(true);
               
               $manager->persist($doc);
               $manager->flush();
               $this->addFlash('success','the document has been moved to the trash bin  successfully');
           }
   
                return $this->redirectToRoute('trash.list');
        }



#[Route('/restore/{id<\d+>}', name: 'Doc.restore.trash')]
public function restoreDoc(ManagerRegistry $doctrine ,Document $doc=null):RedirectResponse
{
   

  
    if($doc)
   {
    
       $manager=$doctrine->getManager();

       $doc->setIsDeleted(false);
       $doc->setIsAff(false);

       $manager->persist($doc);
       $manager->flush();
       $this->addFlash('success','the document has been restored successfully');
   }

   return $this->redirectToRoute('doc.display',['id'=>$doc->getId(),'vs'=>$doc->getCurrentVersion()]);
}


#[Route('/deletefromtrash/{id<\d+>}', name: 'Doc.delete_from_trash')]
    public function deleteDoc(ManagerRegistry $doctrine ,Document $doc=null):RedirectResponse
    {
       if($doc)
       {
        
           #$parent=$folder->getParentFolder();
            $manager=$doctrine->getManager();
            $manager->remove($doc);
   
           $manager->flush();
          foreach($doc->getDocVersions() as $vs)
            {
                $path = $this->getParameter('docs_directory').'original\\'.$vs->getFileName() ;
                if(file_exists($path)) unlink($path);

                $file_pdf = pathinfo( $vs->getFileName())['filename'].'.pdf'; 
                $path = $this->getParameter('docs_directory').'converted\\'.$file_pdf;
                if(file_exists($path)) unlink($path);

            }

           $this->addFlash('success','the document has been deleted successfully');
       }else{
           $this->addFlash('error',"an error occured please try aggain ");
       }
         return $this->redirectToRoute('trash.list');
    }


    #[Route('/display/{id}/{vs?0}', name: 'doc.display')]
    public function previewDoc(Document $doc=null,$vs,ManagerRegistry $doctrine):Response
    {   
        $repository=$doctrine->getRepository(DocVersion::class);
      
        $versions=$repository->findBy(['doc'=>$doc],['createdAt'=>'DESC']);

   
       
        if($vs=='0')
        {
            $vs=$doc->getCurrentVersion();
        }
        return $this->render('document/show.html.twig',['document'=>$doc,'version'=>$vs,'versions'=>$versions]);    
    }

    #[Route('/show/{id<\d+>}/{vs<\d+>}', name: 'doc.show')]
    public function getcontent(Document $doc=null,ManagerRegistry $doctrine,$vs):Response
    {
        $repository=$doctrine->getRepository(DocVersion::class);
      
        $name=$repository->findOneBy(['doc'=>$doc ,'number'=>$vs])->getFileName();       
        $path = $this->getParameter('docs_directory').$name ;


        $fileEncryptor = new FileEncryptor( $name);


        $path_parts = pathinfo( $path);
        $path_parts = pathinfo( $path);

        $filename= $path_parts['filename'].'.pdf'; 

     
          $content =  $fileEncryptor->decryptFile(  $this->getParameter('docs_directory').'converted\\'.$filename);
          $filename= $name; 

        
       
        
        $response = new Response($content);
    
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            $name
        );
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', $disposition);

    

        return $response;

    }
    #[Route('/deleteversion/{id<\d+>}/{vs<\d+>}', name: 'Doc.deleteversion')]
    public function deleteversion(ManagerRegistry $doctrine,Document $doc=null,$vs):Response
    {
       
        $repository=$doctrine->getRepository(DocVersion::class);
       
        $version=$repository->findBy(['doc'=>$doc ,'number'=>$vs])['0'];
       
        $doc->removeDocVersion($version);
        $manager=$doctrine->getManager();
        $manager->remove($version);
        $manager->flush();
        $path = $this->getParameter('docs_directory').'original\\'.$version->getFileName() ;
        if(file_exists($path)) unlink($path);

        $file_pdf = pathinfo( $version->getFileName())['filename'].'.pdf'; 
        $path = $this->getParameter('docs_directory').'converted\\'.$file_pdf;
        if(file_exists($path)) unlink($path);

         return $this->redirectToRoute('doc.display',['id'=>$doc->getId(),'vs'=>$doc->getCurrentVersion()]);
        
    }

    #[Route('/restoreversion/{id<\d+>}/{vs<\d+>}', name: 'Doc.restoreversion')]
    public function restoreVersion(ManagerRegistry $doctrine,Document $doc=null,$vs):Response
    {
       
        $repository=$doctrine->getRepository(DocVersion::class);
       
        $version=$repository->findOneBy(['doc'=>$doc ,'number'=>$vs]);
        
       
        $version->setnumber($doc->getCurrentVersion()+1);
        $doc->setCurrentVersion($doc->getCurrentVersion()+1);
        $doc->setStorageName($version->getFileName());

        $manager=$doctrine->getManager();
        $manager->persist($doc);
        $manager->persist($version);
        $manager->flush();


         return $this->redirectToRoute('doc.display',['id'=>$doc->getId(),'vs'=>$doc->getCurrentVersion()]);
        
    }

    #[Route('/search', name: 'Doc.search' ,methods: ['POST'])]
    public function searchDocs(Request $request, EntityManagerInterface $entityManager): Response
    {
        $searchQuery = $request->request->get('search');
        
        $queryBuilder = $entityManager->createQueryBuilder()
            ->select('e')
            ->from(Document::class, 'e')
            ->where('UPPER(e.textContent) LIKE UPPER(:searchQuery) or UPPER(e.name) LIKE UPPER(:searchQuery)')
            ->andwhere('e.folder is not null')
            ->setParameter('searchQuery', '%' . $searchQuery . '%')
            ->orderBy('e.updatedAt', 'DESC');

        $results = $queryBuilder->getQuery()->getResult();


        return $this->render('document/search.html.twig', [
            'results' => $results,
        ]);
    }


}
