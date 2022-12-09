<?php

namespace App\SheetParsers;

use App\Types\Shift;
use DateInterval;
use DateTimeImmutable;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class PilgrimEmergency extends AbstractSheetParser {
    protected static string $parserName = "Pilgrim - Emergency Medicine";
    protected static string $parserSlug = "pilgrim_emergencymedicine";

    public function getShifts(): array {
        $headerCell = $this->findCellInRow($this->sheet, 1, $this->nameFilter);
        $shiftColumn = $headerCell->getColumn();
        $shiftCells = $this->getColumn($shiftColumn, 2);
        $dateCells = $this->getColumn('A', 2);
        /** @var Cell[][] $combinedShiftCells */
        $combinedShiftCells = array_map(null, $dateCells, $shiftCells);

        $shifts = [];

        foreach ($combinedShiftCells as [$dateCell, $shiftCell]) {
            /** @var DateTimeImmutable $shiftStart */
            /** @var DateInterval $shiftLength */
            $shiftsData = $this->getShiftTimes($shiftCell->getValue(), Date::excelToDateTimeObject($dateCell->getValue()));
            foreach ($shiftsData as $shiftData) {
                if ($shiftData[1] === null) {
                    continue;
                }
                $shift = new Shift($shiftData[0], $shiftData[1], $shiftData[2]);
                $shifts[] = $shift;
            }
        }

        return $shifts;
    }

    private function getShiftTimes(string $cellValue, \DateTime $startDate): array {
        $regex = '(\d\d)(\d\d)-(\d\d)(\d\d)(\sstart at (\d\d)(\d\d)\))?';
        if (str_contains($cellValue, "off")) {
            return [["Off", null, null]];
        }

        $matches = [];
        preg_match($regex, $cellValue, $matches);

        $shiftStart = clone $startDate;
        $shiftStart->setTime($matches[1], $matches[2]);
        $shiftEnd = clone $startDate;
        $shiftEnd->setTime($matches[3], $matches[4]);

        if (str_contains($cellValue, "start at")) {
            $sdtStart = clone $startDate;
            $sdtStart->setTime($matches[1], $matches[2]);
            $sdtEnd = clone $startDate;
            $sdtStart->setTime($matches[6], $matches[7]);
            $shiftStart->setTime($matches[6], $matches[7]);
            return [
                ["SDT", $sdtStart, $sdtEnd],
                ["In", $shiftStart, $shiftEnd],
            ];
        }
        return [
            ["In", $shiftStart, $shiftEnd],
        ];
    }

}