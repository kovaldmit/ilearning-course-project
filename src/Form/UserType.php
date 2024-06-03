<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control']
            ])
            ->add('fullName', TextType::class, [
                'label' => 'Full Name',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control']
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Password',
                'required' => false,
                'mapped' => false,
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
