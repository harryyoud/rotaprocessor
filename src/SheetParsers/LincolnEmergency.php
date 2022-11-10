<?php

namespace App\SheetParsers;

use App\Types\Shift;
use DateTimeImmutable;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class LincolnEmergency extends AbstractSheetParser {
    protected static string $parserName = "Lincoln - Emergency Medicine";
    protected static string $parserSlug = "lincoln_emergencymedicine";

    public function getShifts(): array {
        $dateCells = [];
        $lastRow = 1;
        foreach ($this->sheet->getRowIterator(2) as $row) {
            foreach ($row->getCellIterator('A', 'A') as $cell) {
                $lastRow = $cell->getRow();
                if ($cell->getFormattedValue() === "") {
                    break 2;
                }
                $dateCells[] = $cell;
            }
        }

        $lastCol = 'B';
        foreach ($this->getRow(1, 'C') as $cell) {
            $lastCol = $cell->getColumn();
            if (empty($cell->getValue())) {
                break;
            }
        }

        $shiftCells = [];

        $color = null;

        foreach ($this->sheet->getRowIterator(2) as $row) {
            if ($row->getRowIndex() >= $lastRow) {
                break;
            }
            foreach ($row->getCellIterator('C', $lastCol) as $cell) {
                $cellColor = $this->sheet->getStyle($cell->getCoordinate())->getFill()->getStartColor()->getARGB();
                if (str_contains($cell->getValue() ?? '', $this->nameFilter) || ($color !== null && $cellColor === $color)) {
                    $color = $cellColor;
                    $shiftCells[] = [
                        'dateCell' => $dateCells[$row->getRowIndex() - 2],
                        'timeCell' => $this->sheet->getCell($cell->getColumn() . '1'),
                        'shiftCell' => $cell,
                    ];
                }
            }
        }

        $shifts = [];
        foreach ($shiftCells as $shiftData) {
            $startDate = Date::excelToDateTimeObject($shiftData['dateCell']->getValue());
            $timeData = $shiftData['timeCell']->getValue();
            $matches = [];
            preg_match('/(\d\d):(\d\d)-(\d\d):(\d\d)/', $timeData, $matches);
            $startDate->setTime($matches[1], $matches[2]);

            $endDate = Date::excelToDateTimeObject($shiftData['dateCell']->getValue());
            $endDate->setTime($matches[3], $matches[4]);

            if (str_contains($shiftData['shiftCell']->getValue(), 'SDT')) {
                $matches = [];
                $regex = '/SDT (\d\d)\.(\d\d)-(\d\d)\.(\d\d)\s?\/(\d\d)\.(\d\d)-(\d\d)\.(\d\d) ON SHIFT/';
                preg_match($regex, $shiftData['shiftCell']->getValue(), $matches);
                $startDate->setTime($matches[5], $matches[6]);
                $endDate->setTime($matches[7], $matches[8]);
                $sdtStart = clone $startDate;
                $sdtEnd = clone $endDate;
                $sdtStart->setTime($matches[1], $matches[2]);
                $sdtEnd->setTime($matches[3], $matches[4]);
                $shifts[] = new Shift(
                    'SDT',
                    DateTimeImmutable::createFromMutable($sdtStart),
                    DateTimeImmutable::createFromMutable($sdtEnd),
                );
            } else {
                if (!str_contains($shiftData['shiftCell']->getValue(), $this->nameFilter)) {
                    continue;
                }
                $matches = [];
                $regex = '/.*(\d\d)\.(\d\d)-(\d\d)\.(\d\d)/';
                if (preg_match($regex, $shiftData['shiftCell']->getValue(), $matches) === 1) {
                    $startDate->setTime($matches[1], $matches[2]);
                    $endDate->setTime($matches[3], $matches[4]);
                    printf("%s - %s - %s\n",
                        $startDate->format('Y-m-d H:i'),
                        $shiftData['shiftCell']->getValue(),
                        Date::excelToDateTimeObject($shiftData['dateCell']->getValue())->format('Y-m-d'),
                    );
                }
            }

            $shifts[] = new Shift(
                str_contains($shiftData['shiftCell']->getValue(), '(RESUS)') ? 'Resus' : 'In',
                DateTimeImmutable::createFromMutable($startDate),
                DateTimeImmutable::createFromMutable($endDate),
            );
        }

        return $shifts;
    }
}