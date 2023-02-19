<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;

class SignupType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options): void {
        $builder
            ->add('name', TextType::class, [
                'attr' => ['autocomplete' => 'name',],
            ])
            ->add('email', TextType::class, [
                'attr' => ['autocomplete' => 'email',],
                'constraints' => [
                    new Email(),
                ]
            ])
            ->add('password', RepeatedType::class, [
                'attr' => ['autocomplete' => 'off',],
                'type' => PasswordType::class,
                'mapped' => false,
                'first_options' => [
                    'label' => 'Password',
                ],
                'second_options' => [
                    'label' => 'Repeat password',
                ],
            ])
            ->add('save', SubmitType::class);
    }
}