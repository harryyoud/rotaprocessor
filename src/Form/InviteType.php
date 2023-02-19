<?php

namespace App\Form;

use App\Entity\WebDavCalendar;
use App\SheetParsers\SheetParser;
use App\SheetParsers\SheetParsers;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class InviteType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options): void {
        $builder
            ->add('comment', TextType::class)
            ->add('save', SubmitType::class);
    }
}