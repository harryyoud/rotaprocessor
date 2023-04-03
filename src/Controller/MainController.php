<?php

namespace App\Controller;

use App\Entity\Placement;
use App\Entity\SyncJob;
use App\Entity\User;
use App\Entity\WebCalCalendar;
use App\Entity\WebDavCalendar;
use App\Form\DeleteEntityType;
use App\Form\PlacementType;
use App\Form\RotaSheetType;
use App\Form\WebDavCalendarType;
use App\Message\NewRotaFileNotification;
use App\Security\CalendarVoter;
use App\Security\PlacementVoter;
use App\SheetParsers\SheetParsers;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;
use function Doctrine\ORM\QueryBuilder;

#[Route('/my')]
#[IsGranted('ROLE_USER')]
class MainController extends AbstractController {
    public function __construct(
        private readonly SheetParsers           $parsers,
        private readonly EntityManagerInterface $em,
        private readonly KernelInterface        $kernel,
        private readonly MessageBusInterface    $bus,
        private readonly PaginatorInterface     $paginator,
    ) {
    }

    #[Route('/placements', name: 'list_placements')]
    public function listPlacements(): Response {
        $placements = $this->em->createQueryBuilder()
            ->select('p')
            ->from(Placement::class, 'p')
            ->where('p.owner = :owner')
            ->setParameter('owner', $this->getUser()->getId()->toBinary())
            ->orderBy('p.name', 'DESC')
            ->getQuery()->execute();
        $parsers = $this->parsers->getParsers();
        return $this->render('placements.html.twig', [
            'placements' => $placements,
            'parsers' => $parsers,
        ]);
    }

    protected function getUser(): ?User {
        $user = parent::getUser();
        if ($user !== null && !$user instanceof User) {
            throw new \Exception("Unexpected user type!");
        }
        return $user;
    }

    #[Route('/placement/{id}/jobs', name: 'list_jobs_by_placement')]
    #[IsGranted(PlacementVoter::VIEW_JOBS, subject: 'placement')]
    public function listJobsByPlacement(Placement $placement, Request $request): Response {
        $qb = $this->em->createQueryBuilder()
            ->select('j')
            ->from(SyncJob::class, 'j')
            ->where('j.placement = :placement')
            ->setParameter('placement', $placement->getId()->toBinary())
            ->orderBy('j.createdAt', 'DESC');
        $parsers = $this->parsers->getParsers();
        $pagination = $this->paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('jobs.html.twig', [
            'pagination' => $pagination,
            'placement' => $placement,
            'parsers' => $parsers,
        ]);
    }

    #[Route('/placement/new', name: 'new_placement')]
    public function newPlacement(Request $request): Response {
        return $this->editPlacement(new Placement(), $request);
    }

    #[Route('/placement/{id}/edit', name: 'edit_placement')]
    #[IsGranted(PlacementVoter::EDIT, subject: 'placement')]
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
            $placement->setOwner($this->getUser());
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

    #[Route('/calendars', name: 'list_calendars')]
    public function listCalendars(): Response {
        $calendars = $this->getUser()->getCalendars();
        return $this->render('calendars.html.twig', [
            'calendars' => $calendars,
        ]);
    }

    #[Route('/calendar/new', name: 'new_calendar')]
    public function newCalendar(Request $request): Response {
        return $this->editCalendar(new WebDavCalendar(), $request);
    }

    #[Route('/calendar/{id}/edit', name: 'edit_calendar')]
    #[IsGranted(CalendarVoter::EDIT, subject: 'calendar')]
    public function editCalendar(WebDavCalendar $calendar, Request $request): Response {
        $isNew = is_null($calendar->getId());
        $form = $this->createForm(WebDavCalendarType::class, $calendar, [
            'new_calendar' => $isNew,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var WebDavCalendar $calendar */
            $calendar = $form->getData();
            if (!is_null($form->get('password')->getData()) && !empty($form->get('password')->getData())) {
                $calendar->setPassword($form['password']->getData());
            }
            $calendar->setOwner($this->getUser());
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

    #[Route('/jobs', name: 'list_jobs')]
    public function listJobs(Request $request): Response {
        $qb = $this->em->createQueryBuilder()
            ->select('j')
            ->from(SyncJob::class, 'j')
            ->where('j.owner = :owner')
            ->setParameter('owner', $this->getUser()->getId()->toBinary())
            ->orderBy('j.createdAt', 'DESC');
        $parsers = $this->parsers->getParsers();
        $pagination = $this->paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('jobs.html.twig', [
            'pagination' => $pagination,
            'parsers' => $parsers,
        ]);
    }

    #[Route('/jobs/pending', name: 'list_pending_jobs')]
    public function listIncompleteJobs(): Response {
        $qb = $this->em->createQueryBuilder();
        $qb = $qb
            ->select('j')
            ->from(SyncJob::class, 'j')
            ->where($qb->expr()->andX(
                $qb->expr()->orX(
                    $qb->expr()->eq('j.status', SyncJob::STATUS_PENDING),
                    $qb->expr()->eq('j.status', SyncJob::STATUS_CREATED),
                ),
                $qb->expr()->eq('j.owner', $this->getUser()->getId()->toBinary()),
            ));
        $jobs = $qb->getQuery()->execute();;
        $parsers = $this->parsers->getParsers();
        return $this->render('jobs.html.twig', [
            'jobs' => array_reverse($jobs),
            'parsers' => $parsers,
        ]);
    }

    #[Route('/placements/{id}/upload', name: 'upload')]
    #[IsGranted(PlacementVoter::UPLOAD, subject: 'placement')]
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
            $filename = Uuid::v4()->toRfc4122() . '.' . $extension;
            $file->move($folder, $filename);

            $job = new SyncJob($placement, $filename);
            $job->setOwner($this->getUser());
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
    #[IsGranted(PlacementVoter::DELETE, subject: 'placement')]
    public function deletePlacement(Placement $placement, Request $request): Response {
        $form = $this->createForm(DeleteEntityType::class, []);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->remove($placement);
            foreach ($placement->getJobs() as $job) {
                $this->em->remove($job);
            }
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
    #[IsGranted(CalendarVoter::DELETE, subject: 'calendar')]
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
}
