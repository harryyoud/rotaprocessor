<?php

namespace App\SheetParsers;

use App\Types\Shift;
use DateInterval;
use DateTimeImmutable;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class LincolnMedicine extends AbstractSheetParser {
    protected static string $parserName = "Lincoln - Medicine";
    protected static string $parserSlug = "lincoln_medicine";

    public function getShifts(): array {
        $nameCell = self::findCellInCol($this->sheet, 'B', $this->nameFilter);
        $ourRowIdx = $nameCell->getRow();

        $shiftCells = $this->getRow($ourRowIdx, 'C');
        $dateCells = $this->getRow(3, 'C');

        $shifts = [];

        foreach (array_map(null, $dateCells, $shiftCells) as [$dateCell, $shiftCell]) {
            /** @var Cell $dateCell */
            /** @var Cell $shiftCell */
            if ($dateCell === null || $shiftCell === null) {
                continue;
            }
            $shiftDate = $this->getFixedDateValue($dateCell);
            [$shiftName, $shiftStartTime, $shiftLength] = $this->getShiftInfo($shiftCell->getFormattedValue());
            if ($shiftStartTime === null) {
                continue;
            }
            $shiftStart = $shiftDate->setTime($shiftStartTime->format('H'), $shiftStartTime->format('i'), $shiftStartTime->format('s'));
            $shiftEnd = $shiftStart->add($shiftLength);
            $shifts[] = new Shift($shiftName, $shiftStart, $shiftEnd);
        }

        return $shifts;
    }

    private function getFixedDateValue(Cell $dateCell): \DateTimeImmutable {
        $dateValue = $dateCell->getOldCalculatedValue();
        if (empty($dateValue)) {
            $dateValue = $dateCell->getValue();
        }
        return DateTimeImmutable::createFromMutable(Date::excelToDateTimeObject($dateValue));
    }

    private function getShiftInfo(string $shiftCode): array {
        return match ($shiftCode) {
            'B' => ['Ward', new DateTimeImmutable('09:00:00'), new DateInterval('PT8H')],
            'B + T' => ['Ward (teaching day)', new DateTimeImmutable('09:00:00'), new DateInterval('PT8H')],
            'OFF' => ['Off', null, null],
            'SDT' => ['SDT', new DateTimeImmutable('09:00:00'), new DateInterval('PT8H')],
            'TWFY1' => ['MEAU twilight', new DateTimeImmutable('16:00:00'), new DateInterval('PT8H')],
            'DFY1' => ['MEAU long day', new DateTimeImmutable('09:00:00'), new DateInterval('PT12H30M')],
        };
    }
}