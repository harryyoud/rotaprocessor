<?php

namespace App\Command;

use App\Entity\Placement;
use App\Entity\WebDavCalendar;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:placement:create',
    description: 'Create a placement and persist it in the database',
)]
class PlacementCreateCommand extends Command {

    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        $this
            ->addArgument('name', InputArgument::REQUIRED, 'Name of placement')
            ->addArgument('processor', InputArgument::REQUIRED, 'Processor slug')
            ->addArgument('calendarCategory', InputArgument::REQUIRED, 'Calendar category')
            ->addArgument('prefix', InputArgument::REQUIRED, 'Prefix')
            ->addArgument('nameFilter', InputArgument::REQUIRED, 'Name filter')
            ->addArgument('sheetName', InputArgument::REQUIRED, 'Sheet name')
            ->addArgument('calendarId', InputArgument::REQUIRED, 'Calendar ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $io = new SymfonyStyle($input, $output);
        $name = $input->getArgument('name');
        $processor = $input->getArgument('processor');
        $calendarCategory = $input->getArgument('calendarCategory');
        $prefix = $input->getArgument('prefix');
        $nameFilter = $input->getArgument('nameFilter');
        $sheetName = $input->getArgument('sheetName');
        $calendarId = $input->getArgument('calendarId');
        $calendar = $this->em->find(WebDavCalendar::class, intval($calendarId));

        $placement = new Placement();
        $placement->setName($name);
        $placement->setProcessor($processor);
        $placement->setCalendarCategory($calendarCategory);
        $placement->setPrefix($prefix);
        $placement->setNameFilter($nameFilter);
        $placement->setSheetName($sheetName);
        $placement->setCalendar($calendar);
        $this->em->persist($placement);
        $this->em->flush();

        $io->writeln("Placement created successfully!");

        return Command::SUCCESS;
    }
}
