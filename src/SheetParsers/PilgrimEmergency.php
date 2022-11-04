<?php

namespace App\SheetParsers;

class PilgrimEmergency extends AbstractSheetParser {
    protected static string $parserName = "Pilgrim - Emergency Medicine (TBC)";
    protected static string $parserSlug = "pilgrim_emergencymedicine";

    public function getShifts(): array {
        return [];
    }
}