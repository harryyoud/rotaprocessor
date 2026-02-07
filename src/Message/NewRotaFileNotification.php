<?php

namespace App\Message;

use Symfony\Component\Uid\Uuid;

class NewRotaFileNotification {
    public function __construct(private readonly Uuid $jobId)
    {
    }

    public function getJobId(): Uuid {
        return $this->jobId;
    }
}
