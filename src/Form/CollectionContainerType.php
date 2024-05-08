<?php

namespace App\Form;

use App\Entity\CollectionCategory;
use App\Entity\CollectionContainer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class CollectionContainerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Length([
                        'max' => 255,
                    ]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Length([
                        'max' => 255,
                    ]),
                ],
            ])
            ->add('collectionCategory', EntityType::class, [
                'class' => CollectionCategory::class,
                'choice_label' => 'name',
                'label' => 'Category',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control']
            ])
            ->add('image', FileType::class, [
                'label' => 'Image',
                'mapped' => false,
                'required' => false,
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CollectionContainer::class,
        ]);
    }
}
