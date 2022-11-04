<?php

namespace App\SheetParsers;

class LincolnEmergency extends AbstractSheetParser {
    protected static string $parserName = "Lincoln - Emergency Medicine (TBC)";
    protected static string $parserSlug = "lincoln_emergencymedicine";

    public function getShifts(): array {
        return [];
    }
}