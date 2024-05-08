<?php

namespace App\Form;

use App\Entity\CollectionCustomFieldValue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class CustomFieldValueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('value', TextType::class, [
                'label' => $options['label'],
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Length([
                        'max' => 255,
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CollectionCustomFieldValue::class,
        ]);
    }
}
