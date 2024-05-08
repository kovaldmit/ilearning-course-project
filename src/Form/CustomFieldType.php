<?php

namespace App\Form;

use App\Entity\CollectionCustomField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class CustomFieldType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Field Name',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Length([
                        'max' => 255,
                    ]),
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Field Type',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control'],
                'choices' => [
                    'Text' => 'text',
                    'Textarea' => 'textarea',
                    'Number' => 'number',
                    'Date' => 'date',
                    'Boolean' => 'boolean',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CollectionCustomField::class,
        ]);
    }
}
