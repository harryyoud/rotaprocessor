<?php

namespace App\Controller;

use App\Entity\SyncJob;
use App\Security\SyncJobVoter;
use JsonException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class ApiController extends AbstractController {
    public function __construct() {
    }

    #[Route('/job/{id}/log', name: 'get_job_log_json')]
    #[IsGranted(SyncJobVoter::VIEW_LOGS, subject: 'job')]
    public function getJobLog(SyncJob $job): Response {
        return new Response($job->getLog(), 200, ["Content-Type" => "text/plain"]);
    }
}