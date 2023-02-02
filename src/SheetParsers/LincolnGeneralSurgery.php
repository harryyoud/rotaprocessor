<?php

namespace App\SheetParsers;

use DateInterval;
use DateTimeImmutable;

class LincolnGeneralSurgery extends PilgrimGeneralSurgery {
    protected static string $parserName = "Lincoln - General Surgery";
    protected static string $parserSlug = "lincoln_gensurg";

    protected function getShiftTimes(string $cellValue, \DateTime $startDate): array {
        return match ($cellValue) {
            'SEAU' => ['SEAU', new DateTimeImmutable($startDate->format('Y-m-d'), '08:00:00'), new DateInterval('PT12H30M')],
            'Nights' => ['Nights', new DateTimeImmutable($startDate->format('Y-m-d'), '20:00:00'), new DateInterval('PT12H30M')],
            'Cover' => ['Cover', new DateTimeImmutable($startDate->format('Y-m-d'), '08:00:00'), new DateInterval('PT12H30M')],
            default => parent::getShiftTimes($cellValue, $startDate),
        };
    }
}