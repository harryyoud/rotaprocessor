<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WebDavCalendarType extends AbstractType {
    public function __construct() {}

    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        $builder
            ->add("name", TextType::class)
            ->add("url", TextType::class, [
                "help" => "URL for CalDAV calendar",
            ])
            ->add("username", TextType::class, [
                "help" => "Username for CalDAV calendar",
            ])
            ->add("password", PasswordType::class, [
                "help" =>
                    "Password for CalDAV calendar. Leave blank to keep unchanged",
                "mapped" => false,
                "required" => $options["new_calendar"],
            ])
            ->add("color", ColorType::class, [
                "help" => "Colour used for events on the calendar",
                "label" => "Colour",
            ])
            ->add("save", SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void {
        $resolver->setDefaults([
            "new_calendar" => false,
        ]);
    }
}
