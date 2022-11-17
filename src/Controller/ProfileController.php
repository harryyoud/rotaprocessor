<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController {
    public function __construct(
        private readonly EntityManagerInterface      $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    #[Route('/profile', name: 'profile')]
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
        return $this->renderForm('profile.html.twig', [
            'form' => $form
        ]);

    }
}