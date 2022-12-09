<?php

namespace App\Message;

use Symfony\Component\Uid\Uuid;

class NewRotaFileNotification {
    private Uuid $jobId;

    public function __construct(Uuid $jobId) {
        $this->jobId = $jobId;
    }

    public function getJobId(): Uuid {
        return $this->jobId;
    }
}
