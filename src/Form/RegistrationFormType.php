<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
      
            ->add('firstName',TextType::class,[
                'attr'=>[ 
                    'class'=>'border mt-2 mx-2',
                    'autofocus'=>'true',
                    'minlength'=>2,
                    'maxlength'=>50,
                    'placeholder'=>'Enter your firstname...',
                    'required'=>'true'
                ]
            ])
            ->add('lastName',TextType::class,[
                'attr'=>[ 
                    'class'=>'border mt-2 mx-2 ',
                    'minlength'=>2,
                    'maxlength'=>50,
                    'required'=>'true',
                    'placeholder'=>'Enter your lastname...'
                ]
            ])
            ->add('photo', FileType::class, [
                'label' => 'choose your profile picture (only  images are accepted)',
                // unmapped means that this field is not associated to any entity property
                'mapped' => false,
                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,
                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '10000k',
                        'mimeTypes' => [
                            'image/gif',
                            'image/jpeg',
                            'image/png',
                            'image/jpg',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid Image',
                    ])
                ],
            ])
            ->add('phoneNumber')
            ->add('email',EmailType::class,[
                'attr'=>[ 
                    'class'=>'border mt-2 mx-2',
                    'required'=>'true',
                    'placeholder'=>'Enter your email...'
                ]
            ])
            ->add('plainPassword', PasswordType::class,[
                    'mapped' => false,
            ])
            ->add('Roles', ChoiceType::class, [
                'required' => true,
                'multiple' => true,
                'expanded' => true,
                'label'=>false,
                'choices'  => [
                    'all Permissions(admin)' => 'ROLE_ADMIN',
                    'manage users' => 'ROLE_manage_users',
                    'manage all groups' => 'ROLE_manage_groups',
                    'create groups' => 'ROLE_create_groups',
                    'manage all folders' => 'ROLE_manage_folders',
                    'Upload files' => 'ROLE_upload',
                    'classify files' => 'ROLE_classify'

                ],
            ])
            ->add('submit',SubmitType::class,[
                

            ]);
    
    }
    

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
