<?php

namespace App\Controller;

use App\Entity\AccessRight;
use App\Entity\Document;
use App\Entity\Folder;
use App\Entity\Notification;
use App\Entity\User;
use App\Form\FolderEditType;
use App\Form\FolderType;
use App\Repository\FolderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/folder'),IsGranted('IS_AUTHENTICATED_FULLY')]
class FolderController extends AbstractController
{
    
    #[Route('/treeView/{selectedFolderId?0}', name: 'folder.treeView')]
    public function getFolders(ManagerRegistry $doctrine,Security $security,$selectedFolderId): Response
    {
       
            $repository=$doctrine->getRepository(Folder::class);
            $folder=$repository->findOneBy(['name'=>'root']);

        /** @var User $user */
            $user = $security->getUser();


       $MyCreatedFolderList=$repository->findBy(['createdBy'=>$user,'IsDeleted'=>false]);


       $accessRights=  $user->getAccessRights();

       $myFolderList=[];
       
       
       for($i=0;$i<count($accessRights);$i++)
       {
        if(! in_array($accessRights[$i]->getFolder(),$myFolderList))
         array_unshift($myFolderList,$accessRights[$i]->getFolder());
    }

   

       $MyGroups= $user->getGroupes() ;
      
        for($i=0;$i<count($MyGroups);$i++)
        {  
         
           $var= $MyGroups[$i]->getAccessRights();
        
            for($j=0;$j<count($var);$j++)
            {  
                if(! in_array($var[$j]->getFolder(),$myFolderList))
              {  array_unshift($myFolderList,$var[$j]->getFolder());
            
                }
               
            }
        }

    

        return $this->render('folder/treeView.html.twig', [
            'folder'=>$folder,
            'MyCreatedFolderList'=>$MyCreatedFolderList,
            'myAccessedFolderList'=>$myFolderList ,
            'id'=>$selectedFolderId
        ]);





    }
    #[Route('/add/{parentFolder<\d+>}', name: 'folder.add')]
    public function addFolder(ManagerRegistry $doctrine,Request $request,Security $security,$parentFolder):Response
    {   
        $folder=new folder();

        $WriteAccesRight=new AccessRight();
        $WriteAccesRight->setFolder($folder);
        $WriteAccesRight->setMode(2);
        $folder->addAccessRight($WriteAccesRight);

        $ReadAccesRight=new AccessRight();
        $ReadAccesRight->setFolder($folder);
        $ReadAccesRight->setMode(1);
        $folder->addAccessRight($ReadAccesRight);

        
        $form=$this->createForm(FolderType::class,$folder,[
            'action'=>$this->generateUrl('folder.add',['parentFolder'=>$parentFolder])
        ]);
        $form->remove('parentFolder');
        $form->handleRequest($request);
        

        if($form->isSubmitted()&& $form->isValid())
        {   
            $folder->setCreatedBy($security->getUser());
            $entityManager=$doctrine->getManager();
            

            $repository=$doctrine->getRepository(Folder::class);
            $parent=$repository->findOneBy(['id'=>$parentFolder]);
            $folder->setParentFolder($parent);
            $entityManager->persist($folder);
            $notif=new Notification();
            $notif->setUser($this->getUser());
            $notif->setFolder($folder);
            $notif->setAction('added a new  folder (📁'.$folder->getName().') in (📁'.$folder->getParentFolder()->getName().')');
            $entityManager->persist($notif);
            $entityManager->flush();
         
            
            $this->addFlash('success',"The folder has been added  successfully  ");
           
            return $this->redirectToRoute('folder.treeView',[  'selectedFolderId'=>$parentFolder]);
         

        }else{
            return $this->render('folder/add.html.twig',['form'=>$form->createView(),'action'=>'Add']);
            
        }
    }

  
    #[Route('/delete/{id<\d+>}', name: 'folder.delete')]
    public function deletetotrash(ManagerRegistry $doctrine,Folder $folder=null,Folder $folder2=null, FolderRepository $folderRepository):RedirectResponse
    {
        $manager=$doctrine->getManager();
        $folder->setIsAff(true);
        $manager->persist($folder);
        $manager->flush();

        $folderRepository->deleteFolderAndChildren($folder);

           $this->addFlash('success','the folder has been moved to the trash bin successfully');
 
            return $this->redirectToRoute('trash.list');
    }

    #[Route('/edit/{id?0<\d+>}', name: 'folder.edit')]
    public function editFolder(ManagerRegistry $doctrine , Folder $folder=null , Request $request , EntityManagerInterface $entityManager): Response
    {   
      
        $forme=$this->createForm(FolderEditType::class,$folder,[
            'action'=>$this->generateUrl('folder.edit',['id'=>$folder->getId()])
        ]);
        
        $forme->remove('parentFolder');
        if($folder->getName()=='root')
           $forme->remove('name');

        $forme->handleRequest($request);
        if($forme->isSubmitted() && $forme->isValid())
        {   
           
            $message=" has been updated  successfully";
            

            $entityManager=$doctrine->getManager();
            $entityManager->persist($folder);
            $notif=new Notification();
            $notif->setUser($this->getUser());
            $notif->setFolder($folder);
            $notif->setAction('edited the folder (📁'.$folder->getName().')');
            $entityManager->persist($notif);

            $entityManager->flush();

            $name=$folder->getName();
            $this->addFlash('success',"$name $message");

            return $this->redirectToRoute('folder.treeView',[  'selectedFolderId'=>$folder->getId()]);
        }
        return $this->render('folder/edit.html.twig',['form'=>$forme->createView(),'action'=>'Edit' , 'folder'=>$folder]);
    }

#[Route('/trash', name: 'trash.list')]
public function foldersTrash(ManagerRegistry $doctrine,Security $security): Response
{ 
       $repository=$doctrine->getRepository(Folder::class);
 
    
        if ($this->isGranted('ROLE_ADMIN')) 
        $trash=$repository->findBy(['IsAff'=>true,'IsDeleted'=>true]);
        else
        $trash=$repository->findBy(['IsAff'=>true,'IsDeleted'=>true,'createdBy'=>$security->getUser()]);

        $repository=$doctrine->getRepository(Document::class);
 
    
        if ($this->isGranted('ROLE_ADMIN')) 
        $trashdoc=$repository->findBy(['IsAff'=>true,'IsDeleted'=>true]);
        else
        $trashdoc=$repository->findBy(['IsAff'=>true,'IsDeleted'=>true,'createdBy'=>$security->getUser()]);

       return $this->render('folder/trashList.html.twig', [
        'trashlist' => $trash,
        'trashlistdoc' => $trashdoc,
        'type'=>'deleted'
       ]);
    
}

#[Route('/restore/{id<\d+>}', name: 'restore.trash')]
public function restoreFolder(ManagerRegistry $doctrine ,Folder $folder=null, FolderRepository $folderRepository):RedirectResponse
{
    $manager=$doctrine->getManager();
    $folder->setIsAff(false);
    $manager->persist($folder);
    $manager->flush();

    $folderRepository->restoreFolderAndChildren($folder);

        return $this->redirectToRoute('folder.treeView');
}


#[Route('/deletefromtrash/{id<\d+>}', name: 'folder.delete_from_trash')]
    public function deleteFolder(ManagerRegistry $doctrine ,Folder $folder=null):RedirectResponse
    {
       if($folder)
       {
        
           #$parent=$folder->getParentFolder();
            $manager=$doctrine->getManager();
            $manager->remove($folder);
   
           $manager->flush();
           $this->addFlash('success','the folder has been deleted successfully');
       }else{
           $this->addFlash('error',"an error occured please try aggain ");
       }
         return $this->redirectToRoute('trash.list');
    }

    #[Route('/folder/{id<\d+>}', name: 'folder.folder')]
    public function folder(Folder $folder=null,ManagerRegistry $doctrine): Response
    {
        $repository=$doctrine->getRepository(Folder::class);
        $folderList =$repository->findBy(['parentFolder'=>$folder,'IsDeleted'=>false]);
        return $this->render('folder/tt.html.twig', [
            'folderlist' => $folderList,
            'folder'=>$folder,
           
        ]);
    }



    #[Route('/foldersList', name: 'folder.list')]
    public function folderList(Folder $folder=null,ManagerRegistry $doctrine,Security $security): Response
    {
       
            $repository=$doctrine->getRepository(Folder::class);
            $folder=$repository->findOneBy(['name'=>'root']);
        

        $repository=$doctrine->getRepository(Folder::class);

        /** @var User $user */
            $user = $security->getUser();


       $MyCreatedFolderList=$repository->findBy(['createdBy'=>$user,'IsDeleted'=>false]);


       $accessRights=  $user->getAccessRights();

       $myFolderList=[];
       
       
       for($i=0;$i<count($accessRights);$i++)
       {
        if(! in_array($accessRights[$i]->getFolder(),$myFolderList))
         array_unshift($myFolderList,$accessRights[$i]->getFolder());
    }

   

       $MyGroups= $user->getGroupes() ;
      
        for($i=0;$i<count($MyGroups);$i++)
        {  
         
           $var= $MyGroups[$i]->getAccessRights();
        
            for($j=0;$j<count($var);$j++)
            {  
                if(! in_array($var[$j]->getFolder(),$myFolderList))
              {  array_unshift($myFolderList,$var[$j]->getFolder());
            
                }
               
            }
        }


        return $this->render('folder/folderList.html.twig', [
            'folder'=>$folder,
            'MyCreatedFolderList'=>$MyCreatedFolderList,
            'myAccessedFolderList'=>$myFolderList 
        ]);
    }

    #[Route('/addToFavorite/{id}', name: 'folder.favorite')]
    public function favorite(Folder $folder=null,ManagerRegistry $doctrine,Security $security): Response
    {
        $manager=$doctrine->getManager();
          
            
           /** @var User $user */
           $user = $security->getUser();
           if( $user->getFavorites()->contains($folder))
          { $user->removeFavorite($folder);
            $manager->persist($user);
            $manager->flush();
             return $this->render('folder/invavorites.html.twig',['in'=>false,'folder'=>$folder]);
        }

           else
           $user->addFavorite($folder);
          
           $manager->persist($user);
           $manager->flush();
           return $this->render('folder/invavorites.html.twig',['in'=>true,'folder'=>$folder]);

}


#[Route('/favfolders', name: 'fav_folders')]
public function favoritelist(): Response
{
    
    return $this->render('home/favfolders.html.twig');    
}
}