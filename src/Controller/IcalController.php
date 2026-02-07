<?php

namespace App\Controller;

use App\Entity\Placement;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class IcalController extends AbstractController {
    #[Route("/placement/{id}/ical", name: "ical_placement")]
    public function getIcal(Placement $placement): Response {
        if (!is_null($placement->getCalendar())) {
            throw $this->createNotFoundException(
                "Placement has a WebDav calendar",
            );
        }

        $shifts = json_decode((string) $placement->getShifts());

        $response = $this->render("ical.ics.twig", [
            "shifts" => $shifts,
            "placement" => $placement,
        ]);
        $response->headers->set("Content-Type", "text/calendar");
        return $response;
    }
}
