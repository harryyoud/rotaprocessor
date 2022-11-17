<?php

namespace App\MessageHandler;

use App\Entity\Placement;
use App\Entity\SyncJob;
use App\Entity\WebCalCalendar;
use App\Entity\WebDavCalendar;
use App\Message\NewRotaFileNotification;
use App\SheetParsers\SheetParsers;
use Doctrine\ORM\EntityManagerInterface;
use Error;
use Exception;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Process\Process;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use Throwable;

#[AsMessageHandler]
class ProcessNewRotaFile {
    public function __construct(
        private readonly SheetParsers $parsers,
        private readonly EntityManagerInterface $em,
        private readonly KernelInterface $kernel,
    ) {}

    public function __invoke(NewRotaFileNotification $message): void {
        $job = $this->em->find(SyncJob::class, $message->getJobId());
        $job->markPending();
        $this->em->persist($job);
        $this->em->flush();

        try {
            $placement = $job->getPlacement();
            $parser = $this->parsers->getParser($placement->getProcessor());
            if (is_null($parser)) {
                throw new Error("Null parser");
            }
            $sheet = $this->loadSheet($this->kernel->getProjectDir() . "/var/upload/" . $job->getFilename(), $placement->getSheetName());

            $parser->setSheet($sheet);
            $parser->setNameFilter($placement->getNameFilter());

            $shifts = $parser->getShifts();
            $calendar = $placement->getCalendar();

            if (is_null($calendar)) {
                $out = $this->handleIcal($placement, $shifts);
            } else {
                $out = $this->handleCaldav($calendar, $placement, $shifts);
            }
            try {
                unlink($this->kernel->getProjectDir() . "/var/upload/" . $job->getFilename());
            } catch (Exception $e) {}

            $result = json_decode($out, associative: true);

            if (array_key_exists("error", $result)) {
                $job->markFailed($out);
            } else {
                $job->markSuccess($out);
            }

        } catch (Throwable $e) {
            $job->markFailed(json_encode([
                'type' => $e::class,
                'where' => 'PHP - '.$e->getFile().':'.$e->getLine(),
                'detail' => $e->getMessage(),
                'reason' => 'No reason',
            ]));
        }

        $this->em->persist($job);
        $this->em->flush();
    }

    private function handleIcal(Placement $placement, $shifts) {
        $encoders = array(new JsonEncoder());
        $normalizers = array(new DateTimeNormalizer(), new GetSetMethodNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $dataJson = $serializer->serialize($shifts, 'json');
        $placement->setShifts($dataJson);
        $this->em->persist($placement);
        $this->em->flush();
        return json_encode(['message' => 'Updated iCal with '.count($shifts).' shifts']);
    }

    private function handleCaldav(WebDavCalendar $calendar, Placement $placement, $shifts) {
        $data = [
            'calendar' => [
                'url' => $calendar->getUrl(),
                'username' => $calendar->getUsername(),
                'password' => $calendar->getPassword(),
                'category' => $placement->getCalendarCategory(),
                'prefix' => $placement->getPrefix(),
                'color' => $calendar->getColor(),
            ],
            'shifts' => $shifts,
        ];

        $encoders = array(new JsonEncoder());
        $normalizers = array(new DateTimeNormalizer(), new GetSetMethodNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $dataJson = $serializer->serialize($data, 'json');

        $process = new Process([$this->kernel->getProjectDir() . '/' . 'exe/sync_calendar.py']);
        $process->setInput($dataJson);
        $process->mustRun();
        $process->wait();
        return $process->getOutput();
    }

    private function loadSheet(string $fileName, string $sheetName): Worksheet {
        $reader = new Xlsx();
        $reader->setLoadSheetsOnly($sheetName);
        $workbook = $reader->load($fileName);
        return $workbook->getActiveSheet();
    }
}