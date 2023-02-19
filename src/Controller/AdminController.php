<?php

namespace App\Controller;

use App\Entity\Invite;
use App\Entity\User;
use App\Form\DeleteEntityType;
use App\Form\InviteType;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController {
    public function __construct(
        private readonly EntityManagerInterface      $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly PaginatorInterface          $paginator,
    ) {
    }

    #[Route('/users', name: 'list_users')]
    public function listUsers(): Response {
        $users = $this->em->getRepository(User::class)->findAll();
        return $this->render('users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/user/{id}/delete', name: 'delete_user')]
    public function deleteUser(User $user, Request $request): Response {
        $form = $this->createForm(DeleteEntityType::class, []);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            foreach ([$user->getPlacements(), $user->getCalendars(), $user->getJobs()] as $item) {
                $this->em->remove($item);
            }
            $this->em->remove($user);
            $this->em->flush();
            $this->addFlash("success", "User deleted successfully");
            return $this->redirectToRoute('list_users');
        }
        return $this->renderForm('user_delete.html.twig', [
            'placement' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/user/new', name: 'new_user')]
    public function newUser(Request $request): Response {
        return $this->editUser(new User(), $request);
    }

    #[Route('/user/{id}/edit', name: 'edit_user')]
    public function editUser(User $user, Request $request): Response {
        $isNew = is_null($user->getId());
        $form = $this->createForm(UserType::class, $user, [
            'new_user' => $isNew,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $form->getData();
            if (!is_null($form->get('password')->getData()) && !empty($form->get('password')->getData())) {
                $hashedPassword = $this->passwordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData(),
                );
                $user->setPassword($hashedPassword);
            }
            $this->em->persist($user);
            $this->em->flush();
            if ($isNew) {
                $this->addFlash("success", "User created successfully");
            } else {
                $this->addFlash("success", "User changes submitted");
            }
            return $this->redirectToRoute('list_users');
        }
        return $this->renderForm('user_edit.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/invites', name: 'list_invites')]
    public function getInviteLinks(Request $request): Response {
        $qb = $this->em->createQueryBuilder()
            ->select('i')
            ->from(Invite::class, 'i')
            ->orderBy('i.createdAt', 'DESC');
        $pagination = $this->paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('invites.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/invite/{id}/revoke', name: 'revoke_invite')]
    public function revokeInvite(Invite $invite, Request $request): Response {
        $form = $this->createForm(DeleteEntityType::class, []);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $invite->setUsed(true);
            $invite->setEmailUsed("-revoked-");
            $invite->setUsedAt(new \DateTimeImmutable());
            $this->em->persist($invite);
            $this->em->flush();
            $this->addFlash("success", "Invite revoked successfully");
            return $this->redirectToRoute('list_invites');
        }
        return $this->renderForm('invite_revoke.html.twig', [
            'invite' => $invite,
            'form' => $form,
        ]);
    }

    #[Route('/invite/new', name: 'new_invite')]
    public function newInviteLink(Request $request): Response {
        $form = $this->createForm(InviteType::class, new Invite());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Invite $invite */
            $invite = $form->getData();
            $invite->setCreatedAt(new \DateTimeImmutable());
            $this->em->persist($invite);
            $this->em->flush();
            $this->addFlash("success", "Invite created successfully");
            return $this->redirectToRoute('list_invites');
        }
        return $this->renderForm('invite_new.html.twig', [
            'form' => $form
        ]);
    }
}