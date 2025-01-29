<?php

namespace App\Form;

use App\Entity\Document;
use App\Entity\Folder;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class DocType extends AbstractType
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
            'placeholder'=>'Enter a new name or keep original file name...',
            'required'=>'true'
        ]])
            ->add('folder',EntityType::class,[
                'expanded' => false,
                'class' => Folder::class,
                'label' =>'location',
                'multiple' => false,
                'required' => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('f')
                        ->orderBy('f.name', 'ASC');
                },
                'choice_label' => 'name',
                'attr' => [
                    'class' => 'select2'
                ]
            ])
          
            ->add('file', FileType::class, [
                'label' => 'upload a file',
                // unmapped means that this field is not associated to any entity property
                'mapped' => false,
                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => true,
                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '102400K',
                    ])
                ],
            ])
            ->add('submitadd',SubmitType::class,[
                'label'=>"Add Doc",
              //  'attr'=>['hx-post'=>$options['action'],'type'=>'submit']
                
            ])

            ->add('submitedit',SubmitType::class,[
                'label'=>"Edit Doc",
                //'attr'=>['hx-post'=>$options['action'],'type'=>'submit']
                
            ])

            
        ;
    }
    public function getBlockPrefix()
    {
        return '';
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Document::class,
        ]);
    }
}