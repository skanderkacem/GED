<?php

namespace App\Form;

use App\Entity\AccessRight;
use App\Entity\Folder;
use App\Entity\Groupe;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FolderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name',TextType::class,['label'=>false,
            'attr'=>[ 
                'class'=>'border mt-2 mx-2',
                'autofocus'=>'true',
                'minlength'=>2,
                'maxlength'=>50,
                'placeholder'=>'Enter folder name...',
                'required'=>'true',
                'pattern'=>"^(?!.*\broot\b).+$"
            ]])
            ->add('parentFolder',EntityType::class,[
                'expanded' => false,
                'class' => Folder::class,
                'multiple' => false,
                'required' => true,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('f')
                        ->orderBy('f.name', 'ASC');
                },
                'choice_label' => 'name',
                'attr' => [
                    'class' => 'select2'
                ]
            ])
            ->add('accessRights',CollectionType::class,['entry_type'=>AccessRightType::class,
            'allow_add'=>true, 'allow_delete'=>true, 'by_reference'=>true
            ])
            ->add('submit',SubmitType::class,[
                'label'=>"Add Folder",
                
            ])
    ;


    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Folder::class,
        ]);
    }
}
