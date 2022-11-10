<?php

namespace App\Controller;

use App\Entity\Placement;
use App\Entity\SyncJob;
use App\Entity\WebCalCalendar;
use App\Entity\WebDavCalendar;
use App\Form\DeleteEntityType;
use App\Form\PlacementType;
use App\Form\RotaSheetType;
use App\Form\WebDavCalendarType;
use App\Message\NewRotaFileNotification;
use App\SheetParsers\SheetParsers;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/')]
class IndexController extends AbstractController {
    public function __construct(
        private readonly SheetParsers $parsers,
        private readonly EntityManagerInterface $em,
        private readonly KernelInterface $kernel,
        private readonly MessageBusInterface $bus,
    ) {}

    #[Route('/placements', name: 'list_placements')]
    public function listPlacements(): Response {
        $placements = $this->em->getRepository(Placement::class)->findAll();
        $parsers = $this->parsers->getParsers();
        return $this->render('placements.html.twig', [
            'placements' => $placements,
            'parsers' => $parsers,
        ]);
    }

    #[Route('/placement/{id}/edit', name: 'edit_placement')]
    public function editPlacement(Placement $placement, Request $request): Response {
        $isNew = is_null($placement->getId());
        $form = $this->createForm(PlacementType::class, $placement);
        $prevCalendar = $placement->getCalendar();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Placement $placement */
            $placement = $form->getData();
            if ($prevCalendar === null && $placement->getCalendar() !== null) {
                $placement->setShifts(null);
            }
            $this->em->persist($placement);
            $this->em->flush();
            if ($isNew) {
                $this->addFlash("success", "Placement created successfully");
            } else {
                $this->addFlash("success", "Placement changes submitted");
            }
            return $this->redirectToRoute('list_placements');
        }
        return $this->renderForm('placement_edit.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/placement/{id}/jobs', name: 'list_jobs_by_placement')]
    public function listJobsByPlacement(Placement $placement): Response {
        $jobs = $placement->getJobs();
        $parsers = $this->parsers->getParsers();
        return $this->render('jobs.html.twig', [
            'jobs' => array_reverse($jobs),
            'placement' => $placement,
            'parsers' => $parsers,
        ]);
    }

    #[Route('/placement/new', name: 'new_placement')]
    public function newPlacement(Request $request): Response {
        return $this->editPlacement(new Placement(), $request);
    }

    #[Route('/calendars', name: 'list_calendars')]
    public function listCalendars(): Response {
        $calendars = $this->em->getRepository(WebDavCalendar::class)->findAll();
        return $this->render('calendars.html.twig', [
            'calendars' => $calendars,
        ]);
    }

    #[Route('/calendar/{id}/edit', name: 'edit_calendar')]
    public function editCalendar(WebDavCalendar $calendar, Request $request): Response {
        $isNew = is_null($calendar->getId());
        $form = $this->createForm(WebDavCalendarType::class, $calendar, [
            'new_calendar' => $isNew,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $calendar = $form->getData();
            if (!is_null($form->get('password')->getData()) && !empty($form->get('password')->getData())) {
                $calendar->setPassword($form['password']->getData());
            }
            $this->em->persist($calendar);
            $this->em->flush();
            if ($isNew) {
                $this->addFlash("success", "Calendar created successfully");
            } else {
                $this->addFlash("success", "Calendar changes submitted");
            }
            return $this->redirectToRoute('list_calendars');
        }
        return $this->renderForm('calendar_edit.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/calendar/new', name: 'new_calendar')]
    public function newCalendar(Request $request): Response {
        return $this->editCalendar(new WebDavCalendar(), $request);
    }

    #[Route('/jobs', name: 'list_jobs')]
    public function listJobs(): Response {
        $jobs = $this->em->getRepository(SyncJob::class)->findAll();
        $parsers = $this->parsers->getParsers();
        return $this->render('jobs.html.twig', [
            'jobs' => array_reverse($jobs),
            'parsers' => $parsers,
        ]);
    }

    #[Route('/jobs/pending', name: 'list_pending_jobs')]
    public function listIncompleteJobs(): Response {
        $jobs = $this->em->createQueryBuilder()
            ->select('j')
            ->from('App:SyncJob', 'j')
            ->where('j.status = ?', SyncJob::STATUS_CREATED)
            ->orWhere('j.status = ?', SyncJob::STATUS_PENDING)
            ->getQuery()
            ->execute();
        ;
        $parsers = $this->parsers->getParsers();
        return $this->render('jobs.html.twig', [
            'jobs' => $jobs,
            'parsers' => $parsers,
        ]);
    }

    #[Route('/placements/{id}/upload', name: 'upload')]
    public function newUpload(Placement $placement, Request $request): Response {
        $stuff = [];

        $form = $this->createForm(RotaSheetType::class, $stuff);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['file']->getData();
            $extension = $file->guessExtension();
            if (!$extension) {
                // extension cannot be guessed
                $extension = 'bin';
            }
            $folder = $this->kernel->getProjectDir() . '/var/upload/';
            $filename = Uuid::v4()->toRfc4122() .'.'.$extension;
            $file->move($folder, $filename);

            $job = new SyncJob($placement, $filename);
            $this->em->persist($job);
            $this->em->flush();
            $this->bus->dispatch(new NewRotaFileNotification($job->getId()));

            $this->addFlash("success", "Job submitted; now awaiting processing");
            return $this->redirectToRoute('list_jobs_by_placement', ['id' => $placement->getId()]);
        }
        return $this->renderForm('upload.html.twig', [
            'placement' => $placement,
            'form' => $form,
        ]);
    }

    #[Route('/placement/{id}/delete', name: 'delete_placement')]
    public function deletePlacement(Placement $placement, Request $request): Response {
        $form = $this->createForm(DeleteEntityType::class, []);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->remove($placement);
            $this->em->flush();
            $this->addFlash("success", "Placement deleted successfully");
            return $this->redirectToRoute('list_placements');
        }
        return $this->renderForm('placement_delete.html.twig', [
            'placement' => $placement,
            'form' => $form,
        ]);
    }

    #[Route('/calendar/{id}/delete', name: 'delete_calendar')]
    public function deleteCalendar(WebDavCalendar $calendar, Request $request): Response {
        $form = $this->createForm(DeleteEntityType::class, []);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (count($calendar->getPlacements()) > 0) {
                $this->addFlash("danger", "Calendar has attached placements, please delete these first");
                return $this->renderForm('calendar_delete.html.twig', [
                    'calendar' => $calendar,
                    'form' => $form,
                ]);
            }
            $this->em->remove($calendar);
            $this->em->flush();
            $this->addFlash("success", "Calendar deleted successfully");
            return $this->redirectToRoute('list_calendars');
        }
        return $this->renderForm('calendar_delete.html.twig', [
            'calendar' => $calendar,
            'form' => $form,
        ]);
    }

    #[Route('/', name: 'landing')]
    public function landing(): Response {
        return $this->render('landing.html.twig');
    }
}