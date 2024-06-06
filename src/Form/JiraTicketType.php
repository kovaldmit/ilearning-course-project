<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class JiraTicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Length([
                        'max' => 255,
                    ]),
                ],
                'required' => true,
            ])
            ->add('priority', ChoiceType::class, [
                'label' => 'Priority',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control'],
                'choices' => $this->getPriorityChoices($options['priorities']),
            ])
            ->add('url', HiddenType::class, [
                'label' => 'Page URL',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control'],
                'data' => $options['current_url'],
                'required' => false,
            ])
        ;
    }

    private function getPriorityChoices(array $priorities): array
    {
        $choices = [];
        foreach ($priorities as $priority) {
            $choices[$priority->name] = $priority->name;
        }
        return $choices;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'priorities' => [],
            'current_url' => null,
        ]);
    }
}
