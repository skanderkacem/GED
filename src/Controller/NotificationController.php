<?php

namespace App\Controller;

use App\Entity\AccessRight;
use App\Entity\Notification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Select;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NotificationController extends AbstractController
{
    #[Route('/notifications', name: 'app_notification')]
    public function send(ManagerRegistry $doctrine): Response
    {
        $repository=$doctrine->getRepository(Notification::class);
        $notifications=$repository->findBy([],['createdAt'=>'DESC'],20);
        return $this->render('notification/notifications.html.twig',['notifications'=>$notifications]);    
    
}

    #[Route('/notificationsDash', name: 'dash_notification')]
    public function consult(ManagerRegistry $doctrine): Response
    {
        $repository=$doctrine->getRepository(Notification::class);
        $notifications=$repository->findBy([],['createdAt'=>'DESC'],50);  
        return $this->render('notification/notificationsDash.html.twig',['notifications'=>$notifications]);    
    }
}
