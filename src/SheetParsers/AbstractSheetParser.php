<?php

namespace App\SheetParsers;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

abstract class AbstractSheetParser implements SheetParser {
    protected static string $parserName;
    protected static string $parserSlug;
    protected static string $sheetName;
    protected Worksheet $sheet;
    protected string $nameFilter;

    public static function getParserName(): string {
        return static::$parserName;
    }

    public static function getSheetName(): string {
        return static::$sheetName;
    }

    public static final function getParserSlug(): string {
        return static::$parserSlug;
    }

    /**
     * Finds first cell in specified row matching string in $exactContents, throws error if none found
     * @param Worksheet $sheet
     * @param int $rowIdx
     * @param string $regex
     * @return Cell
     */
    protected static function findCellInRow(Worksheet $sheet, int $rowIdx, string $exactContents): Cell {
        $foundCell = null;

        $row = $sheet->getRowIterator(startRow: $rowIdx, endRow: $rowIdx + 1)->current();
        foreach ($row->getCellIterator('A') as $cell) {
            if ($exactContents === $cell->getValue()) {
                $foundCell = $cell;
                break;
            }
        }
        unset($row);

        if ($foundCell === null) {
            throw new \ValueError("Unable to find cell matching name filter");
        }

        return $foundCell;
    }

    /**
     * Finds first cell in specified row matching string in $exactContents, throws error if none found
     * @param Worksheet $sheet
     * @param string $columnIdx
     * @param string $exactContents
     * @return Cell
     */
    protected static function findCellInCol(Worksheet $sheet, string $columnIdx, string $exactContents): Cell {
        $foundCell = null;

        foreach ($sheet->getRowIterator(startRow: 1) as $row) {
            foreach ($row->getCellIterator($columnIdx, $columnIdx) as $cell) {
                if ($exactContents === $cell->getValue()) {
                    $foundCell = $cell;
                    break 2;
                }
            }
        }
        unset($row);

        if ($foundCell === null) {
            throw new \ValueError("Unable to find cell matching name filter");
        }

        return $foundCell;
    }

    public final function setSheet(Worksheet $sheet): void {
        $this->sheet = $sheet;
    }

    public function setNameFilter(string $regex): void {
        $this->nameFilter = $regex;
    }

    /**
     * @return Cell[]
     */
    protected function getColumn(string $column, int $rowStart): array {
        $cells = [];
        foreach ($this->sheet->getRowIterator($rowStart) as $row) {
            foreach ($row->getCellIterator($column, $column) as $cell) {
                if ($cell->getFormattedValue() === "") {
                    break 2;
                }
                $cells[] = $cell;
            }
        }
        return $cells;
    }

    /**
     * @return Cell[]
     */
    protected function getRow(int $rowIdx, string $colStart): array {
        $cells = [];
        foreach ($this->sheet->getRowIterator($rowIdx, $rowIdx) as $row) {
            foreach ($row->getCellIterator($colStart) as $cell) {
                if ($cell->getFormattedValue() === "") {
                    break 2;
                }
                $cells[] = $cell;
            }
        }
        return $cells;
    }

}
