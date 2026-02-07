<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class IndexController extends AbstractController {
    #[Route("/", name: "landing")]
    public function landing(): Response {
        return $this->render("landing.html.twig");
    }
}
