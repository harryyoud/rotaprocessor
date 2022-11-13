<?php

namespace App\Controller;

use App\Entity\SyncJob;
use App\Security\SyncJobVoter;
use JsonException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

#[Route('/api')]
class ApiController extends AbstractController {
    public function __construct() {}

    #[Route('/job/{id}/log', name: 'get_job_log_json')]
    #[IsGranted(SyncJobVoter::VIEW_LOGS, subject: 'job')]
    public function getJobLog(SyncJob $job): JsonResponse {
        $text = $job->getLog();
        try {
            json_decode($text, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return new JsonResponse([$text]);
        }
        return new JsonResponse($text, json: true);
    }
}