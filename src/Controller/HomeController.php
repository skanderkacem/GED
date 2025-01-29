<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\Folder;
use App\Entity\Reminder;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
#[Route('/home'),IsGranted('IS_AUTHENTICATED_FULLY')]
class HomeController extends AbstractController
{
    #[Route('/', name: 'app.home')]
    public function home(ManagerRegistry $doctrine, EntityManagerInterface $entityManager, Security $security): Response
    {
        $repository=$doctrine->getRepository(Document::class);
        $nbrDocT=count($repository->findBy(['IsDeleted'=>0]))  ;

        $MyDoc=count($repository->findBy(['IsDeleted'=>0 , 'createdBy'=>$security->getUser()]));

        $nbrDocM = count($entityManager->createQueryBuilder()
        ->select('d')
        ->from(Document::class, 'd')
        ->where('d.createdAt >= :startDate')
        ->andWhere('d.createdAt  <= :endDate')
        ->andWhere('d.IsDeleted = 0')
        ->setParameter('startDate',new \DateTime('first day of this month 00:00:00') )
        ->setParameter('endDate', new \DateTime('now'))
        ->getQuery()
        ->getResult());

        $nbrDocD = count($entityManager->createQueryBuilder()
        ->select('d')
        ->from(Document::class, 'd')
        ->where('d.createdAt >= :startDate')
        ->andWhere('d.createdAt  <= :endDate')
        ->andWhere('d.IsDeleted = 0')
        ->setParameter('startDate',new \DateTime('today 00:00') )
        ->setParameter('endDate', new \DateTime('now'))
        ->getQuery()
        ->getResult());

        

        $repository=$doctrine->getRepository(Folder::class);
        $nbrFolder=count($repository->findBy(['IsDeleted'=>0]))  ;
        $MyFolders=count($repository->findBy(['IsDeleted'=>0 , 'createdBy'=>$security->getUser()]));
       
        $reminders = $entityManager->createQueryBuilder()
        ->select('r')
        ->from(Reminder::class, 'r')
        ->where('r.triggeredAt >= :startDate')
        ->andWhere('r.triggeredAt  <= :endDate')
        ->setParameter('startDate', new \DateTime('now')) 
        ->setParameter('endDate', (new \DateTime('now'))->modify('+1 hour'))
        ->getQuery()
        ->getResult();

       
        return $this->render('home/index.html.twig',['nbrDocT'=>$nbrDocT,'nbrDocM'=>$nbrDocM,'nbrDocD'=>$nbrDocD,'nbrFolder'=>$nbrFolder,'myFolders'=>$MyFolders,'myDocs'=>$MyDoc,'reminders'=>$reminders]);
    }


}