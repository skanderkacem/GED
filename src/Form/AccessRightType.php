<?php

namespace App\Form;

use App\Entity\AccessRight;
use App\Entity\Folder;
use App\Entity\Groupe;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccessRightType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('mode', ChoiceType::class, [
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'label'=>false,
                'choices'  => [
                    'Read only' => 1,
                    'Read && Write' => 2,
                ],
            ])
            ->add('users', EntityType::class, [
                'expanded' => false,
                'mapped' => true,
                'class' => User::class,
                'multiple' => true,
                'required' => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.email', 'ASC');
                },
              
                'attr'=>['class' =>'select2' ]
              
            ])
            ->add('groups', EntityType::class, [
                'mapped' => true,
                'expanded' => false,
                'class' => Groupe::class,
                'multiple' => true,
                'required' => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('g')
                        ->orderBy('g.name', 'ASC');
                },
                'choice_label' => 'name',
                'attr'=>['class' =>'select2' ],
                
               
                
            ])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AccessRight::class,
        ]);
    }
}
