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
     * @Route("/profile", name="profile", methods={"GET"})
     */
    public function index(): Response
    {
        $user = $this->getUser(); // Assuming you have user authentication

        if (!$user) {
            throw $this->createNotFoundException('The user does not exist');
        }
        
        return $this->render('profile/index.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/profile/{id}", name="delete_profile", methods={"DELETE"})
     */
    public function delete_profile($id, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser(); // Assuming you have user authentication

        if (!$user) {
            throw $this->createNotFoundException('The user does not exist');
        }

        if($user->getId() !== $id){
            throw $this->createNotFoundException("You cannot delete someone else's account !");
        }
        
        $entityManager->getRepository(User::class)->delete($user);
        
        return $this->redirectToRoute('home');
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
