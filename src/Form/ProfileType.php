<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\Email;

class ProfileType extends AbstractType {
    public function __construct() {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void {
        $builder
            ->add('name', TextType::class, [
                'attr' => [
                    'autocomplete' => 'name',
                ]
            ])
            ->add('email', TextType::class, [
                'attr' => [
                    'autocomplete' => 'email',
                ],
                'constraints' => [
                    new Email(),
                ]
            ])
            ->add('currentPassword', PasswordType::class, [
                'mapped' => false,
                'required' => true,
                'help' => 'Type your current password to save changes',
                'constraints' => [
                    new UserPassword(),
                ],
                'attr' => [
                    'autocomplete' => 'current-password',
                ],
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'required' => false,
                'first_options' => [
                    'label' => 'Password',
                    'help' => 'Leave blank to leave unchanged',
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],
                'second_options' => [
                    'label' => 'Repeat password',
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],
            ])
            ->add('save', SubmitType::class);
    }
}