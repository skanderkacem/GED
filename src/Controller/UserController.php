<?php

namespace App\Controller;

use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/user'), IsGranted('IS_AUTHENTICATED_FULLY')]
class UserController extends AbstractController
{
    #[Route('/profile/{id<\d+>}', name: 'app_userDetails')]
    public function getProfile(ManagerRegistry $doctrine, UserPasswordHasherInterface $userPasswordHasher, User $user = null, Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        $repository = $doctrine->getRepository(User::class);
        $user = $repository->findOneBy(['id' => $id]);

        if (!$user) {
            $this->addFlash('error', "the user with the  $id not found");
            return $this->redirectToRoute('app_admin');
        }


        return $this->render('user/userDetail.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/list', name: 'app_usersList'), IsGranted('ROLE_manage_users')]
    public function getUsers(ManagerRegistry $doctrine, UserPasswordHasherInterface $userPasswordHasher, User $user = null, Request $request, EntityManagerInterface $entityManager): Response
    {
        $repository = $doctrine->getRepository(User::class);
        $usersList = $repository->findBy([],['updatedAt'=>'DESC']);
       
        return $this->render('user/usersList.html.twig', [
            'usersList' => $usersList,
        ]);

    }

    #[Route('/delete/{id<\d+>}', name: 'app.deleteUser'), IsGranted('ROLE_manage_users')]
    public function deleteUser(ManagerRegistry $doctrine, User $user = null, ): RedirectResponse
    {
        if ($user) {


            $manager = $doctrine->getManager();

            $repository = $doctrine->getRepository(ResetPasswordRequest::class);
            $reset = $repository->findBy(['user' => $user->getId()]);
            for ($i = 0; $i < count($reset); $i++) {
                $manager->remove($reset[$i]);
            }
            $manager->remove($user);

            $manager->flush();
            $this->addFlash('success', 'the user account has been deleted successfully');
        } else {
            $this->addFlash('error', "an error occured please try aggain ");
        }
        return $this->redirectToRoute('app_usersList');
    }


    #[Route('/Edit/{id?0<\d+>}', name: 'user.edit')]
    public function editUser(SluggerInterface $slugger,ManagerRegistry $doctrine, UserPasswordHasherInterface $userPasswordHasher, User $user = null, Request $request, EntityManagerInterface $entityManager): Response
    {
        $action = 'edit';
        if (!$user) {
            $user = new User();
            $action = 'add';
        }

        $form = $this->createForm(RegistrationFormType::class, $user,['action'=>$this->generateUrl('user.edit',['id'=>$user->getId()])]);
        $form->remove('plainpassword');
        $form->add('plainPassword', PasswordType::class, [
            // instead of being set onto the object directly,
            // this is read and encoded in the controller
            'required' => false,
            'mapped' => false,
            'attr' => ['autocomplete' => 'new-password'],

        ]);
        if(! $this->isGranted('ROLE_manage_users'))
        $form->remove('Roles');
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('plainPassword')->getData()) {
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
            }
            $photo = $form->get('photo')->getData();
            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($photo) {
                $directory = $this->getParameter('user_photo_directory');
                $originalFilename = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photo->guessExtension();
        
                // Move the file to the directory where brochures are stored
                try {
                    $photo->move(
                        $directory,
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $user->setPhoto($newFilename); 
            }
            $message = " has been updated  successfully";


            $entityManager = $doctrine->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $name = $user->getFirstName() . " " . $user->getLastName();
            $this->addFlash('success', "$name $message");

            if($this->isGranted('ROLE_manage_users')) return $this->redirectToRoute('app_usersList');
            else return $this->redirectToRoute('app_userDetails',['id'=>$user->getId()]);
           
        }
        return $this->render('registration/edit.html.twig', [
            'registrationForm' => $form->createView(),
            'action' => $action,
            'id'=>$user->getId()
        ]);

    }


    #[Route('/Add', name: 'user.add'), IsGranted('ROLE_manage_users')]
    public function addUser(SluggerInterface $slugger,ManagerRegistry $doctrine, UserPasswordHasherInterface $userPasswordHasher, Request $request, EntityManagerInterface $entityManager): Response
    {

        $user = new User();


        $form = $this->createForm(RegistrationFormType::class, $user,['action'=>$this->generateUrl('user.add')]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('plainPassword')->getData()) {
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
            }
            $photo = $form->get('photo')->getData();
            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($photo) {
                $directory = $this->getParameter('user_photo_directory');
                $originalFilename = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photo->guessExtension();
        
                // Move the file to the directory where brochures are stored
                try {
                    $photo->move(
                        $directory,
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $user->setPhoto($newFilename); 
            }else {
                $user->setPhoto('avatar.png'); 

            }

            $message = " has been added  successfully";


            $entityManager = $doctrine->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $name = $user->getFirstName() . " " . $user->getLastName();
            $this->addFlash('success', "$name $message");


            return $this->redirectToRoute('app_usersList');

        }




        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'action' => 'Add'
        ]);

    }





}