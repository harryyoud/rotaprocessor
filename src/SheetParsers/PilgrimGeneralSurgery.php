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
            /** @var DateTimeImmutable $shiftStart */
            /** @var DateInterval $shiftLength */
            [$shiftName, $shiftStart, $shiftLength] = $this->getShiftTimes($shiftCell->getValue(), Date::excelToDateTimeObject($dateCell->getValue()));
            if ($shiftName === "Off") {
                continue;
            }
            $shiftEnd = $shiftStart->add($shiftLength);
            $shift = new Shift($shiftName, $shiftStart, $shiftEnd);
            $shifts[] = $shift;
        }

        return $shifts;
    }

    protected function getShiftTimes(string $cellValue, \DateTime $startDate): array {
        if ($cellValue === "Cover") {
            if (intval($startDate->format('w')) === 5) {
                return ['Vascular Cover', new DateTimeImmutable($startDate->format('Y-m-d') . '11:00:00'), new DateInterval('PT9H')];
            }
            if (intval($startDate->format('w')) === 6 || intval($startDate->format('w')) === 0) {
                return ['Vascular Cover', new DateTimeImmutable($startDate->format('Y-m-d') . '08:00:00'), new DateInterval('PT12H')];
            }
            return ['Vascular Cover', new DateTimeImmutable($startDate->format('Y-m-d') . '11:00:00'), new DateInterval('PT12H')];
        }
        return match ($cellValue) {
            'WARD' => ['Ward', new DateTimeImmutable($startDate->format('Y-m-d') . '08:00:00'), new DateInterval('PT8H')],
            'OFF' => ['Off', null, null],
            'SL' => ['Study leave', new DateTimeImmutable($startDate->format('Y-m-d') . '08:00:00'), new DateInterval('PT8H')],
            'AL' => ['Annual leave', new DateTimeImmutable($startDate->format('Y-m-d') . '08:00:00'), new DateInterval('PT8H')],
            'WRD/SDT' => ['Ward (half-day)', new DateTimeImmutable($startDate->format('Y-m-d') . '08:00:00'), new DateInterval('PT4H')],
            'DAYS' => ['Days', new DateTimeImmutable($startDate->format('Y-m-d') . '08:00:00'), new DateInterval('PT12H')],
            'DAYS 8-4' => ['Day helper', new DateTimeImmutable($startDate->format('Y-m-d') . '08:00:00'), new DateInterval('PT8H')],
        };
    }
}