<?php

namespace App\Controller;

use App\Entity\Invite;
use App\Entity\User;
use App\Form\SignupType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController {

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/signup/invite/{id}', name: 'signup_with_invite')]
    public function signupWithInvite(Invite $invite, Request $request): Response {
        $form = $this->createForm(SignupType::class, new User());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                $form->get('password')->getData(),
            );
            $user->setPassword($hashedPassword);
            $this->em->persist($user);

            $invite->setEmailUsed($user->getEmail());
            $invite->setUsed(true);
            $invite->setUsedAt(new \DateTimeImmutable());
            $this->em->persist($invite);

            $this->em->flush();
            $this->addFlash("success", "User account created successfully");
            return $this->redirectToRoute('app_login');
        }
        return $this->renderForm('signup_with_invite.html.twig', [
            'form' => $form,
            'invite' => $invite,
        ]);
    }
}