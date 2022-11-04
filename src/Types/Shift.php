<?php

namespace App\Types;

use DateTimeImmutable;

class Shift {
    private DateTimeImmutable $start;
    private DateTimeImmutable $end;
    private string $type;

    public function __construct(string $type, DateTimeImmutable $start, DateTimeImmutable $end) {
        $this->type = $type;
        $this->start = $start;
        $this->end = $end;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getStart(): DateTimeImmutable {
        return $this->start;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getEnd(): DateTimeImmutable {
        return $this->end;
    }

    /**
     * @return string
     */
    public function getType(): string {
        return $this->type;
    }

    public function toString(): string {
        return sprintf("%s-%s - %s", $this->start->format('Y-m-d: Hi'), $this->end->format('Hi'), $this->type);
    }
}
