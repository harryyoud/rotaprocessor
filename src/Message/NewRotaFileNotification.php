<?php

namespace App\Message;

class NewRotaFileNotification {
    private int $jobId;

    public function __construct(int $jobId) {
        $this->jobId = $jobId;
    }

    public function getJobId(): int {
        return $this->jobId;
    }
}