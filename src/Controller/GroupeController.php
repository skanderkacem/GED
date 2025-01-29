<?php

namespace App\Controller;

use App\Entity\Groupe;
use App\Entity\User;
use App\Form\GroupeType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Bundle\SecurityBundle\Security ;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/group'),IsGranted('IS_AUTHENTICATED_FULLY')]

class GroupeController extends AbstractController
{
    #[Route('/List', name: 'app_groupList'),IsGranted('ROLE_manage_groups')]
    public function getGroups(ManagerRegistry $doctrine): Response
    {   $repository=$doctrine->getRepository(Groupe::class);
        $groupList=$repository->findAll();

        return $this->render('groupe/groupeList.html.twig', [
            'grouplist' => $groupList,
            'type'=>'all'
        ]);
    }

    #[Route('/MyList', name: 'app_groupMyList'),IsGranted('ROLE_create_groups')]
    public function mylist(ManagerRegistry $doctrine,Security $security): Response
    {   $repository=$doctrine->getRepository(Groupe::class);

        /** @var User $user */
            $user = $security->getUser();
        if(!empty($user)){
            $userId = $user->getId();
                }
        $groupList=$repository->findBy(['creator'=>$userId]);

        return $this->render('groupe/groupeList.html.twig', [
            'grouplist' => $groupList,
            'type'=>'mine'
        ]);
    }


    #[Route('/add/{allOrMine}', name: 'groupe.add'),IsGranted('ROLE_create_groups')]
    public function addGroup(ManagerRegistry $doctrine,Request $request,Security $security,$allOrMine): Response
    {   
        $groupe=new groupe();
        $form=$this->createForm(GroupeType::class,$groupe,['action'=>$this->generateUrl('groupe.add',['allOrMine'=>$allOrMine])]);
        $form->handleRequest($request);
        if($form->isSubmitted()&& $form->isValid())
        {   
            $groupe->setCreator($user = $security->getUser());
            $entityManager=$doctrine->getManager();
            $entityManager->persist($groupe);
            $entityManager->flush();

            $this->addFlash('success',$groupe->getName()."  has been added  successfully");
            if($allOrMine=='all')
            return $this->redirectToRoute('app_groupList');
            else 
            return $this->redirectToRoute('app_groupMyList');

        }else{
            return $this->render('groupe/add.html.twig',['form'=>$form->createView(),'action'=>'add',]);

        }
    }

    #[Route('/delete/{allOrMine}/{id<\d+>}', name: 'group.delete'),IsGranted('ROLE_create_groups')]
    public function deleteGroup(ManagerRegistry $doctrine ,Groupe $group=null,$allOrMine):RedirectResponse
    {
       if($group)
       {
          
   
           $manager=$doctrine->getManager();
           $manager->remove($group);
   
           $manager->flush();
           $this->addFlash('success','the group  has been deleted successfully');
       }else{
           $this->addFlash('error',"an error occured please try aggain ");
       }
       if($allOrMine=='all')
            return $this->redirectToRoute('app_groupList');
            else 
            return $this->redirectToRoute('app_groupMyList');
    }

    #[Route('/edit/{allOrMine}/{id?0<\d+>}', name: 'group.edit'),IsGranted('ROLE_create_groups')]
    public function editGroup(ManagerRegistry $doctrine , Groupe $group=null , Request $request , EntityManagerInterface $entityManager,$allOrMine): Response
    {   $new=false ;

        $action="edit";
        if(!$group)
        {   
            $group=new Groupe();
            $action="add";
        }
        $form=$this->createForm(GroupeType::class,$group,['action'=>$this->generateUrl('group.edit',['allOrMine'=>$allOrMine,'id'=>$group->getId()])]);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {   
           
            $message=" has been updated  successfully";
            

            $entityManager=$doctrine->getManager();
            $entityManager->persist($group);
            $entityManager->flush();

            $name=$group->getName();
            $this->addFlash('success',"$name $message");


            if($allOrMine=='all')
            return $this->redirectToRoute('app_groupList');
            else if ($allOrMine=='mine')
            return $this->redirectToRoute('app_groupMyList');
        }
        return $this->render('groupe/add.html.twig',['form'=>$form->createView(),'action'=>$action]);

}

}