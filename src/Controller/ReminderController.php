<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\Notification;
use App\Entity\Reminder;
use App\Entity\User;
use App\Form\ReminderType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security ;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request ;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/reminder')]
class ReminderController extends AbstractController
{
   
    #[Route('/add/{id}', name: 'addReminder')]
    public function addReminder(ManagerRegistry $doctrine,Request $request,Security $security,Document $doc=null):Response
    {   
        $reminder=new Reminder(); 
      
        $form=$this->createForm(ReminderType::class,$reminder,['action'=>$this->generateUrl('addReminder',['id'=>$doc->getId()])]);
        $form->handleRequest($request);
        

        if($form->isSubmitted()&& $form->isValid())
        {   
            $reminder->setuser($security->getUser());
            $reminder->setDoc($doc);
            $entityManager=$doctrine->getManager();
          
    
           
            $entityManager->persist($reminder);

            $entityManager->persist($reminder);
            $notif=new Notification();
            $notif->setUser($reminder->getUser());
            $notif->setReminder($reminder);
            $notif->setAction('added a reminder on (📄'.$reminder->getDoc()->getName().')');
            $entityManager->persist($notif);
            $entityManager->flush();           
             return $this->redirectToRoute('reminders',['id' =>$doc->getId()]);
         

        }else{
            return $this->render('reminder/Add.html.twig',['form'=>$form->createView(),'id' =>$doc->getId()]);
            
        }
    }   

    #[Route('/edit/{id?0<\d+>}', name: 'reminder.edit')]
    public function editReminder(ManagerRegistry $doctrine , Reminder $reminder=null , Request $request , EntityManagerInterface $entityManager): Response
    {   
      
        $form=$this->createForm(ReminderType::class,$reminder,['action'=>$this->generateUrl('reminder.edit',['id'=>$reminder->getId()])]);
        $form->remove('triggeredAt');
        $form->add('triggeredAt');
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {               $entityManager=$doctrine->getManager();

            $entityManager->persist($reminder);
            $notif=new Notification();
            $notif->setUser($reminder->getUser());
            $notif->setReminder($reminder);
            $notif->setAction('updated a reminder on (📄'.$reminder->getDoc()->getName().')');
            $entityManager->persist($notif);
            $entityManager->flush();
            return $this->redirectToRoute('reminders',['id'=>$reminder->getDoc()->getId()]);
    } else {
       return $this->render('reminder/edit.html.twig',['form'=>$form->createView(),'id'=>$reminder->getId()]);
    }

}


    #[Route('/show/{id}', name:'reminders')] 
    public function getReminders( ManagerRegistry $doctrine,Document $doc=null): Response
    {
        $repository=$doctrine->getRepository(Reminder::class);
      
        $reminders=$repository->findBy(['doc'=>$doc],['triggeredAt'=>'DESC']);
        return $this->render('reminder/reminderlist.html.twig',['reminders'=>$reminders,'id'=>$doc->getId()]);
    
    }
    #[Route('/delete/{id<\d+>}', name: 'deletereminders')]
    public function deleteReminder(ManagerRegistry $doctrine,Reminder $reminder=null):Response
    {
       
      
        $manager=$doctrine->getManager();
        $manager->remove($reminder);
        $manager->flush();
        return $this->redirectToRoute('reminders',['id'=>$reminder->getDoc()->getId()]);
        
    }


}