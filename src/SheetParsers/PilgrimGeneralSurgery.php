<?php

namespace App\SheetParsers;

use App\Types\Shift;
use DateInterval;
use DateTimeImmutable;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class PilgrimGeneralSurgery extends AbstractSheetParser {
    protected static string $parserName = "Pilgrim - General Surgery";
    protected static string $parserSlug = "pilgrim_gensurg";

    public function getShifts(): array {
        $headerCell = $this->findCellInRow($this->sheet, 1, $this->nameFilter);
        $shiftColumn = $headerCell->getColumn();
        $shiftCells = $this->getColumn($shiftColumn, 2);
        $dateCells = $this->getColumn('A', 2);

        /** @var Cell[][] $combinedShiftCells */
        $combinedShiftCells = array_map(null, $dateCells, $shiftCells);

        $shifts = [];

        foreach ($combinedShiftCells as [$dateCell, $shiftCell]) {
            $shiftsToAdd = $this->getShiftTimes($shiftCell->getValue(), Date::excelToDateTimeObject($dateCell->getValue()));
            foreach ($shiftsToAdd as [$shiftName, $shiftStart, $shiftLength]) {
                /** @var DateTimeImmutable $shiftStart */
                /** @var DateInterval $shiftLength */
                if ($shiftName === "Off") {
                    continue;
                }
                $shiftEnd = $shiftStart->add($shiftLength);
                $shift = new Shift($shiftName, $shiftStart, $shiftEnd);
                $shifts[] = $shift;
            }
        }

        return $shifts;
    }

    protected function getShiftTimes(string $cellValue, \DateTime $startDate): array {
        if ($cellValue === "Cover") {
            return [[
                'Vascular Cover',
                new DateTimeImmutable($startDate->format('Y-m-d') . in_array(intval($startDate->format('w')), [0, 6]) ? '08:00:00' : '11:00:00'),
                new DateInterval(intval($startDate->format('w')) === 5 ? 'PT9H' : 'PT12H'),
            ]];
        }
        return match ($cellValue) {
            'OFF' => [['Off', null, null]],
            'WARD' => [['Ward', new DateTimeImmutable($startDate->format('Y-m-d') . '08:00:00'), new DateInterval('PT8H30M')]],
            'SL' => [['Study Leave', new DateTimeImmutable($startDate->format('Y-m-d') . '08:00:00'), new DateInterval('PT8H30M')]],
            'AL' => [['Annual Leave', new DateTimeImmutable($startDate->format('Y-m-d') . '08:00:00'), new DateInterval('PT8H30M')]],
            'WRD/SDT' => [
                ['Ward', new DateTimeImmutable($startDate->format('Y-m-d') . '08:00:00'), new DateInterval('PT4H')],
                ['SDT', new DateTimeImmutable($startDate->format('Y-m-d') . '12:00:00'), new DateInterval('PT4H30M')],
            ],
            'DAYS' => [['Days', new DateTimeImmutable($startDate->format('Y-m-d') . '08:00:00'), new DateInterval('PT12H30M')]],
            'DAYS 8-4' => [['Day Helper', new DateTimeImmutable($startDate->format('Y-m-d') . '08:00:00'), new DateInterval('PT8H30M')]],
        };
    }
}