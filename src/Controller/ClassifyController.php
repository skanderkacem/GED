<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\Folder;
use App\Message\DocUploadMessage;
use App\Service\FileEncryptor;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Messenger\MessageBusInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/classify'), IsGranted('IS_AUTHENTICATED_FULLY')]

class ClassifyController extends AbstractController
{
    #[Route('/', name: 'app_classify'),IsGranted('ROLE_classify')]
    public function classifyDocs(Request $request, ManagerRegistry $doctrine,Security $security): Response
    {
        $repository= $doctrine->getRepository(Folder::class);
        $root=$repository->findOneBy(['name'=>'root','parentFolder'=>null]);
        $form = $this->createFormBuilder()
        ->add('docs', EntityType::class, [
            'expanded' => true,
            'class' => Document::class,
            'multiple' => true,
            'required' => true,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('d')
                    ->where('d.folder IS NULL')
                    ->orderBy('d.createdAt', 'ASC');
            },
           

            'choice_label' => 'name',
            'label' => false,
        ])
        ->add('id',HiddenType::class,['attr' => [
            'class'=>'folderHidden',
            'value' =>"0"
          
        ]])
        ->getForm();
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
          
            $data = $form->getData();

            $entityManager = $doctrine->getManager();
            $repository= $doctrine->getRepository(Document::class);
            foreach ($data['docs'] as $doc) {
                // Use your own logic to set the parent of the document
                $document =$repository->findOneBy(['id' => $doc]);
                

                $parent=$doctrine->getRepository(Folder::class)->findOneBy(['id' => $data['id']]);
                $document->setFolder($parent);
                $doc->setCreatedBy($security->getUser());
                $entityManager->persist($document);
                $entityManager->flush();
            }
                if($data['id']==0)$this->addFlash('warning','no parent folder was selected'); 
                else   $this->addFlash('success','the document   has been classified successfully');


                return $this->redirectToRoute('app_classify');
        }

            return $this->render('classify/adminclassify.html.twig', [
               'root'=> $root,
                'form' => $form->createView(),
            ]);
    }






#[Route('/upload', name: 'app_upload'),IsGranted('ROLE_upload')]
public function UploadFiles(SluggerInterface $slugger,ManagerRegistry $doctrine,Request $request,Security $security ,MessageBusInterface $messageBus): Response
{
$form = $this->createFormBuilder()
->add('files', FileType::class, [
    'label'=>false,
    'multiple' => true,
    'mapped' => false,
    'required' => true,
   
])
->getForm();

$form->handleRequest($request);

if ($form->isSubmitted() && $form->isValid()) {
    $uploadedFiles = $form->get('files')->getData();
    $entityManager=$doctrine->getManager();
  
    for($i=0;$i<count($uploadedFiles);$i++) {
        $doc = new Document();
        

        $originalFilename = pathinfo($uploadedFiles[$i]->getClientOriginalName(), PATHINFO_FILENAME);
        // this is needed to safely include the file name as part of the URL
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'v1.'.$uploadedFiles[$i]->guessExtension() ;

        // Move the file to the directory where brochures are stored
      
            $uploadedFiles[$i]->move(
                $this->getParameter('docs_directory').'original',
                $newFilename
            );
         
            
        $doc->setStorageName($newFilename);
        $doc->setName($newFilename);
    
        $entityManager->persist($doc);
        $entityManager->flush();

        $message = new DocUploadMessage($doc->getId());
        
        $messageBus->dispatch($message);

      
    }
   

    
   
    $this->addFlash('success','the files  has been aded successfully');

        // redirect to the index page or show a success message
        return $this->redirectToRoute('app_upload');
}
// render the form template
return $this->render('classify/classify.html.twig', [
'form' => $form->createView(),
'controller_name' => 'ClassifyController',
]);

}

#[Route('/preview/{id<\d+>}', name: 'doc.classify.preview'),IsGranted('ROLE_classify')]
public function PreviewFile(Document $doc=null):Response
{   

    return $this->render('classify/preview.html.twig',['document'=>$doc]);    
}


#[Route('/showClassify/{id<\d+>}', name: 'classify.show'),IsGranted('ROLE_classify')]
public function showClassify(Document $doc=null):Response
{

    $path = $this->getParameter('docs_directory').$doc->getStorageName() ;

    try{

    $fileEncryptor = new FileEncryptor( $doc->getStorageName() );


    $path_parts = pathinfo( $path);

    $filename= $path_parts['filename'].'.pdf';  
      $content =  $fileEncryptor->decryptFile(  $this->getParameter('docs_directory').'converted\\'.$filename);
      $filename= $doc->getStorageName() ; 

    $response = new Response($content);

    }catch (Exception $e)
    {
        $response = new Response('File is under precessing please come back in a second ');
    }

    $disposition = $response->headers->makeDisposition(
        ResponseHeaderBag::DISPOSITION_INLINE,
        $doc->getStorageName() 
    );
    $response->headers->set('Content-Type', 'application/pdf');
    $response->headers->set('Content-Disposition', $disposition);
    return $response;
    

}

}




