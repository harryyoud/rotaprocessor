<?php

namespace App\Command;

use App\Entity\WebDavCalendar;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:calendar:create',
    description: 'Create a calendar mapping and persist it in the database',
)]
class CalendarCreateCommand extends Command {

    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        $this
            ->addArgument('name', InputArgument::REQUIRED, 'Name of calendar')
            ->addArgument('url', InputArgument::REQUIRED, 'URL')
            ->addArgument('username', InputArgument::REQUIRED, 'Username')
            ->addArgument('password', InputArgument::REQUIRED, 'Password')
            ->addArgument('color', InputArgument::REQUIRED, 'Color');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $io = new SymfonyStyle($input, $output);
        $name = $input->getArgument('name');
        $url = $input->getArgument('url');
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');
        $color = $input->getArgument('color');

        $calendar = new WebDavCalendar();
        $calendar->setName($name);
        $calendar->setUrl($url);
        $calendar->setUsername($username);
        $calendar->setPassword($password);
        $calendar->setColor($color);
        $this->em->persist($calendar);
        $this->em->flush();
        $newId = $calendar->getId();

        $io->writeln("Calendar created successfully! (ID = $newId)");

        return Command::SUCCESS;
    }
}
