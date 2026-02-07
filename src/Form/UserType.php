<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;

class UserType extends AbstractType {
    public function __construct() {}

    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        $builder
            ->add("name", TextType::class, [
                "attr" => ["autocomplete" => "off"],
            ])
            ->add("email", TextType::class, [
                "attr" => ["autocomplete" => "off"],
                "constraints" => [new Email()],
            ])
            ->add("password", RepeatedType::class, [
                "attr" => ["autocomplete" => "off"],
                "type" => PasswordType::class,
                "mapped" => false,
                "required" => $options["new_user"],
                "first_options" => [
                    "label" => "Password",
                    "help" => $options["new_user"]
                        ? ""
                        : "Leave blank to leave unchanged",
                ],
                "second_options" => [
                    "label" => "Repeat password",
                ],
            ])
            ->add("maxInvites", IntegerType::class, [
                "help" =>
                    "Number of invites to create an account this user can create",
            ])
            ->add("admin", CheckboxType::class, [
                "attr" => ["autocomplete" => "off"],
                "required" => false,
                "getter" => fn(
                    User $user,
                    FormInterface $form,
                ) => $user->isAdmin(),
                "setter" => function (
                    User &$user,
                    bool $admin,
                    FormInterface $form,
                ): void {
                    if ($admin) {
                        if (!in_array("ROLE_ADMIN", $user->getRoles())) {
                            $user->setRoles(
                                array_merge($user->getRoles(), ["ROLE_ADMIN"]),
                            );
                        }
                    } else {
                        $user->setRoles(
                            array_filter($user->getRoles(), fn($role) => $role !== "ROLE_ADMIN"),
                        );
                    }
                },
            ])
            ->add("save", SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void {
        $resolver->setDefaults([
            "new_user" => false,
        ]);
    }
}
