<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Invitation;
use App\Repository\UserRepository;
use App\Repository\InvitationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class InvitationController extends AbstractController
{
    /**
     * @Route("/invitation/accept/{token}", name="accept_invitation", methods={"GET", "POST"})
     */
    public function acceptInvitation($token, Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, UserRepository $userRepository)
    {
        $invitation = $entityManager->getRepository(Invitation::class)->findOneBy(['token' => $token]);

        if (!$invitation) {
            throw $this->createNotFoundException('Invitation not found.');
        }

        $user = $userRepository->findOneBy(['email' => $invitation->getEmail()]);

        if ($user) {
            // Existing user
            if ($request->isMethod('POST')) {
                $project = $invitation->getProject();
                $project->addContributor($user);

                $entityManager->persist($project);
                $entityManager->remove($invitation);
                $entityManager->flush();

                return $this->redirectToRoute('home');
            }
        } else {
            // New user
            $user = new User();
            if ($request->isMethod('POST')) {
                $user->setEmail($invitation->getEmail());
                $randomPassword = sha1(random_bytes(18));
                $user->setUsername('user_' . uniqid());
                $user->setPassword($randomPassword);

                $entityManager->persist($user);
                $entityManager->flush();

                $project = $invitation->getProject();
                $project->addContributor($user);

                $entityManager->persist($project);
                $entityManager->remove($invitation);
                $entityManager->flush();

                return $this->redirectToRoute('home');
            }
        }

        return $this->render('invitation/accept.html.twig', [
            'invitation' => $invitation,
            'existingUser' => $user !== null
        ]);
    }

}
