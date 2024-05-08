<?php

namespace App\Form;

use App\Entity\CollectionItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class CollectionItemType extends AbstractType
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
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $item = $event->getData();
            $form = $event->getForm();

            if ($item && $item->getCollectionContainer()) {
                $customFields = $item->getCollectionContainer()->getCustomFields();
                foreach ($customFields as $customField) {
                    $fieldType = match ($customField->getType()) {
                        'textarea' => TextareaType::class,
                        default => TextType::class,
                    };

                    $customFieldValue = $item->getCustomFieldValue($customField);
                    $form->add('customField_' . $customField->getId(), $fieldType, [
                        'label' => $customField->getName(),
                        'label_attr' => ['class' => 'form-label'],
                        'attr' => ['class' => 'form-control'],
                        'mapped' => false,
                        'required' => false,
                        'data' => $customFieldValue ? $customFieldValue->getValue() : null,
                        'constraints' => [
                            new Length([
                                'max' => 255,
                            ]),
                        ],
                    ]);
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CollectionItem::class,
        ]);
    }
}
