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

class PlacementType extends AbstractType {
    public function __construct(
        private readonly SheetParsers $parsers
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void {
        $builder
            ->add('name', TextType::class)
            ->add('processor', ChoiceType::class, [
                'choices' => array_flip(array_map(function(SheetParser $parser) {
                    return $parser::getParserName();
                }, $this->parsers->getParsers())),
            ])
            ->add('calendar', EntityType::class, [
                'class' => WebDavCalendar::class,
                'choice_label' => 'name',
                'placeholder' => 'Built-in WebCal (ICS) Calendar',
                'required' => false,
            ])
            ->add('calendarCategory', TextType::class, [
                'help' => "Category for calendar events",
            ])
            ->add('prefix', TextType::class, [
                'help' => "Prefix to add to calendar events",
                'trim' => false,
            ])
            ->add('nameFilter', TextType::class, [
                'help' => "Exact contents of the row/column containing your shifts",
            ])
            ->add('sheetName', TextType::class, [
                'help' => "Name of the sheet of the Excel file",
            ])
            ->add('save', SubmitType::class)
        ;
    }
}