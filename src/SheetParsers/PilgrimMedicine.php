<?php

namespace App\SheetParsers;

use App\Types\Shift;
use DateTimeImmutable;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use UnhandledMatchError;

class PilgrimMedicine extends AbstractSheetParser {
    protected static string $parserName = "Pilgrim - Medicine";
    protected static string $parserSlug = "pilgrim_medicine";

    public function getShifts(): array {
        $headerCell = $this->findCellInRow($this->sheet, 4, $this->nameFilter);
        $shiftColumn = $headerCell->getColumn();

        $shifts = [];
        $rowNum = 11 - 1;
        $shouldContinue = true;

        do {
            $rowNum += 1;

            /** @var Cell $dateCell */
            $dateCell = $this->sheet->getCell('A'.$rowNum);
            if ($dateCell->getCalculatedValue() === "") {
                $shouldContinue = false;
                continue;
            }
            $startDate = Date::excelToDateTimeObject(intval($dateCell->getCalculatedValue()));

            /** @var Cell $shiftCell */
            $shiftCell = $this->sheet->getCell($shiftColumn.$rowNum);
            $shiftCellValue = $shiftCell->getValue();
            if ($shiftCellValue === null) {
                $shouldContinue = false;
                continue;
            }
            if ($shiftCellValue[0] === '=') {
                $shiftCellValue = $shiftCell->getOldCalculatedValue();
            }

            if ($shiftCellValue === 'Off') {
                continue;
            }

            try {
                $dayType = $this->matchColorToDayType($shiftCell->getStyle()->getFill()->getStartColor()->getRGB());
            } catch (UnhandledMatchError $e) {
                throw new \Error($e->getMessage() . "at cell " . $shiftColumn . $rowNum);
            }
            [$shiftName, $shiftStart, $shiftLength] = $this->matchTextToShift($shiftCellValue, $dayType, $startDate);
            if ($shiftName === null) {
                continue;
            }
            $shiftEnd = $shiftStart->add($shiftLength);
            if ($dayType === null) {
                $shift = new Shift($shiftName, $shiftStart, $shiftEnd);
            } else {
                $shift = new Shift(sprintf("%s (%s)", $shiftName, $dayType), $shiftStart, $shiftEnd);
            }
            $shifts[] = $shift;
        } while ($shouldContinue);

        return $shifts;

    }

    protected function matchColorToDayType(string $rgbColor): ?string {
        return match ($rgbColor) {
            'FFC000' => "Induction",
            'D9D9D9', 'D8D8D8' => "Weekend",
            '00B0F0' => "Teaching day",
            '92D050' => "Bank holiday",
            'FFFF99' => "Prospective Annual Leave",
            'FFFFFF' => null, //Normal day
            'FFFF00' => null,// Annual leave
            'FF66CC' => "Strike",
        };
    }

    protected function matchTextToShift(string $cellValue, ?string $dayType, \DateTime $startDate): array {
        if (in_array($dayType, ['Bank holiday', 'Weekend'])) {
            if ($cellValue === '') {
                return [null, null, null];
            }
        }
        return match ($cellValue) {
            'LD1' => ['LD1 (IAC cover)', new DateTimeImmutable($startDate->format('Y-m-d') . '09:00:00'), new \DateInterval('PT12H30M')],
            'LD2' => ['LD2 (Ward cover)', new DateTimeImmutable($startDate->format('Y-m-d') . '09:00:00'), new \DateInterval('PT12H30M')],
            'Night' => ['Nights (Ward cover)', new DateTimeImmutable($startDate->format('Y-m-d') . '21:00:00'), new \DateInterval('PT12H30M')],
            '' => ['Ward', new DateTimeImmutable($startDate->format('Y-m-d') . '08:30:00'), new \DateInterval('PT8H30M')],
            'IAC' => ['IAC', new DateTimeImmutable($startDate->format('Y-m-d') . '08:30:00'), new \DateInterval('PT8H30M')],
            'AL' => ['Annual Leave', new DateTimeImmutable($startDate->format('Y-m-d') . '08:30:00'), new \DateInterval('PT8H30M')],
            'SD' => ['SDT', new DateTimeImmutable($startDate->format('Y-m-d') . '08:30:00'), new \DateInterval('PT8H30M')],
            'eDD' => ['EDD Cover', new DateTimeImmutable($startDate->format('Y-m-d') . '09:00:00'), new \DateInterval('PT12H')],
        };
    }
}
