<?php

namespace App\SheetParsers;

use App\Types\Shift;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

interface SheetParser {
    public static function getParserName(): string;

    public static function getParserSlug(): string;

    /**
     * Returns parsed shifts from worksheet
     * @return Shift[]
     */
    public function getShifts(): array;

    /**
     * Set a filter for finding names
     * @param string $regex
     * @return void
     */
    public function setNameFilter(string $regex): void;

    public function setSheet(Worksheet $sheet): void;
}
