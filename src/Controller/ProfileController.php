<?php

namespace App\Controller;

use App\Entity\Invite;
use App\Entity\User;
use App\Form\DeleteEntityType;
use App\Form\InviteType;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/profile')]
class ProfileController extends AbstractController {
    public function __construct(
        private readonly EntityManagerInterface      $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    #[Route('/', name: 'profile')]
    public function profile(Request $request): Response {
        $user = $this->getUser();
        $form = $this->createForm(ProfileType::class, $user);
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
            $this->addFlash("success", "User changes submitted");
            return $this->redirectToRoute('profile');
        }
        return $this->render('profile.html.twig', [
            'form' => $form
        ]);

    }

    #[Route('/invites', name: 'my_invites')]
    public function getInviteLinks(): Response {
        $user = $this->getUser();
        if (!($user instanceof User)) {
            throw new HttpException(500, "UserInterface not instance of User");
        }
        $qb = $this->em->createQueryBuilder()
            ->select('i')
            ->from(Invite::class, 'i')
            ->where('i.owner = :owner')
            ->setParameter('owner', $user->getId()->toBinary())
            ->orderBy('i.createdAt', 'DESC');
        return $this->render('invites_mine.html.twig', [
            'invites' => $qb->getQuery()->getResult(),
        ]);
    }

    #[Route('/invite/{id}/revoke', name: 'revoke_my_invite')]
    public function revokeInvite(Invite $invite, Request $request): Response {
        if ($this->getUser() !== $invite->getOwner()) {
            throw $this->createAccessDeniedException("Cannot revoke invite that isn't yours");
        }
        $form = $this->createForm(DeleteEntityType::class, []);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $invite->setUsed(true);
            $invite->setEmailUsed("-revoked-");
            $invite->setUsedAt(new \DateTimeImmutable());
            $this->em->persist($invite);
            $this->em->flush();
            $this->addFlash("success", "Invite revoked successfully");
            return $this->redirectToRoute('my_invites');
        }
        return $this->render('invite_revoke.html.twig', [
            'invite' => $invite,
            'form' => $form,
        ]);
    }


    #[Route('/invite/new', name: 'new_invite')]
    public function newInviteLink(Request $request): Response {
        /** @var User $user */
        $user = $this->getUser();

        if ($user->getMaxInvites() === 0) {
            return $this->render('invite_non_left.html.twig');
        }

        $form = $this->createForm(InviteType::class, new Invite());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Invite $invite */
            $invite = $form->getData();
            $invite->setCreatedAt(new \DateTimeImmutable());
            $invite->setOwner($user);
            $this->em->persist($invite);

            $maxInvites = $user->getMaxInvites();
            if ($maxInvites > 0) {
                $user->setMaxInvites($maxInvites - 1);
            }
            $this->em->persist($user);

            $this->em->flush();
            $this->addFlash("success", "Invite created successfully");
            return $this->redirectToRoute('my_invites');
        }
        return $this->render('invite_new.html.twig', [
            'form' => $form
        ]);
    }
}