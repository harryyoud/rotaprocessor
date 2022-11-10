<?php

namespace App\Form;

use App\Entity\User;
use Doctrine\DBAL\Schema\UniqueConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;

class UserType extends AbstractType {
    public function __construct(
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void {
        $builder
            ->add('name', TextType::class)
            ->add('email', TextType::class, [
                'constraints' => [
                    new Email(),
                ]
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'help' => "Password for CalDAV calendar. Leave blank to keep unchanged",
                'mapped' => false,
                'required' => $options['new_user'],
                'first_options' => [
                    'label' => 'Password',
                ],
                'second_options' => [
                    'label' => 'Repeat password',
                ],
            ])
            ->add('admin', CheckboxType::class, [
                'required' => false,
                'getter' => fn(User $user, FormInterface $form) => $user->isAdmin(),
                'setter' => function (User &$user, bool $admin, FormInterface $form) {
                    if ($admin) {
                        if (!in_array('ROLE_ADMIN', $user->getRoles())) {
                            $user->setRoles(array_merge($user->getRoles(), ['ROLE_ADMIN']));
                        }
                    } else {
                        $user->setRoles(array_filter($user->getRoles(), function ($role) {
                            return $role !== 'ROLE_ADMIN';
                        }));
                    }
                },
            ])
            ->add('save', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'new_user' => false,
        ]);
    }

}