<?php 

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProfileController extends AbstractController
{
    /**
     * @Route("/profile", name="profile")
     */
    public function index(): Response
    {
        $user = $this->getUser(); // Assuming you have user authentication

        return $this->render('profile/index.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/profile/edit", name="profile_edit")
     */
    public function edit(Request $request, EntityManagerInterface $entityManager): Response
    {
        $userId = $this->getUser()->getId();
        $user = $entityManager->getRepository(User::class)->find($userId);

        // Handle the case where the project is not found
        if (!$user) {
            throw $this->createNotFoundException('The user does not exist');
        }

        // Create a form to handle the submission
        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // Redirect back to the project view or handle as needed
            return $this->redirectToRoute('profile');
        }

        return $this->render('profile/_form.html.twig', [
            'form' => $form->createView(),
            'form_action' => $this->generateUrl('profile_edit'),
            'user' => $user 
        ]);
    }

}
