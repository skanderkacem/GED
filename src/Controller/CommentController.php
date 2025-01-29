<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Document;
use App\Entity\Notification;
use App\Entity\User;
use App\Form\CommentType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Length;

class CommentController extends AbstractController
{

    #[Route('/Addcomments/{id}', name:'comment_create', methods:'POST')] 
    public function addComment(Request $request,EntityManagerInterface $entityManager, ManagerRegistry $doctrine,Security $security,Document $doc=null): RedirectResponse
    {
    
        $commentText = $request->request->get('comment');

        // create new comment entity and persist to database
       if(strlen($commentText) > 0 )
       {
        $comment = new Comment();
        $comment->setText($commentText);
          /** @var User $user */
        $user = $security->getUser();
        $comment->setUser($user);
        $comment->setDoc($doc);

        $entityManager=$doctrine->getManager();
        $entityManager->persist($comment);

        $notif=new Notification();
        $notif->setUser($user);
        $notif->setComment($comment);
        $notif->setAction('commented the document (📄'.$doc->getName().') in (📁'.$doc->getFolder()->getName().')');
        $entityManager->persist($notif); 
        
        $entityManager->flush();
       }
        return $this->redirectToRoute('comment_show',['id'=>$doc->getId()]);
    }
    #[Route('/edit/{id?0<\d+>}', name: 'comment.edit')]
    public function editComment(ManagerRegistry $doctrine , Comment $com=null , Request $request , EntityManagerInterface $entityManager): Response
    {   
      
        $form=$this->createForm(CommentType::class,$com,['action'=>$this->generateUrl('comment.edit',['id'=>$com->getId()])]);
        
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {               
            $entityManager=$doctrine->getManager();
            $entityManager->persist($com);

            $notif=new Notification();
            $notif->setUser($com->getUser());
            $notif->setComment($com);
            $notif->setAction('commented the document (📄'.$com->getDOC()->getName().') in (📁'.$com->getDOC()->getFolder()->getName().')');
            $entityManager->persist($notif); 
            

            $entityManager->flush();           
            return $this->redirectToRoute('comment_show',['id'=>$com->getDoc()->getId()]);
    } else {
       return $this->render('comment/editcomment.html.twig',['form'=>$form->createView(),'id'=>$com->getId()]);
    }

}


    #[Route('/showcomments/{id}', name:'comment_show')] 
    public function getComments( ManagerRegistry $doctrine,Document $doc=null): Response
    {
        $repository=$doctrine->getRepository(Comment::class);
      
        $comments=$repository->findBy(['doc'=>$doc],['updatedAt'=>'DESC']);  
        return $this->render('comment/commentlist.html.twig',['comments'=>$comments,'id'=>$doc->getId()]);    
    
    }

    #[Route('/deletecomments/{id<\d+>}', name: 'deletecomments')]
    public function deleteComment(ManagerRegistry $doctrine,Comment $comment=null):Response
    {
       
      
        $manager=$doctrine->getManager();
        $manager->remove($comment);
        $manager->flush();
        return $this->redirectToRoute('comment_show',['id'=>$comment->getDoc()->getId()]);
        
    }
}


