<?php

namespace App\SheetParsers;

use Error;

class SheetParsers {
    /** @var SheetParser[] */
    private array $parsers;

    public function __construct() {
        $this->parsers = [];
    }

    public function addParser(SheetParser $parser): void {
        if (array_key_exists($parser::getParserSlug(), $this->parsers)) {
            throw new Error("Cannot register two parsers with the same slug");
        }
        $this->parsers[$parser::getParserSlug()] = $parser;
    }

    public function getParsers(): array {
        return $this->parsers;
    }

    public function getParser(string $slug): SheetParser|null {
        if (!array_key_exists($slug, $this->parsers)) {
            return null;
        }
        return $this->parsers[$slug];
    }

}