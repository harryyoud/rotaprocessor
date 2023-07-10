<?php

namespace App;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;

class SheetParsers {
    public function __construct(
        private readonly KernelInterface $kernel,
    ) {}

    public function getParsers() {
        $process = new Process([$this->kernel->getProjectDir() . '/' . 'exe/rota-rs', '--get-parsers']);
        $process->mustRun();
        $process->wait();
        $out = [];
        foreach (json_decode($process->getOutput(), associative: true) as $processor) {
            $out[$processor[0]] = $processor[1];
        }
        return $out;
    }
}