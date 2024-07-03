<?php 

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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
    public function delete_profile($id, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, Request $request): Response
    {
        $user = $this->getUser(); // Assuming you have user authentication

        if (!$user) {
            return new JsonResponse(['status' => 'error', 'message' => 'The user does not exist'], Response::HTTP_NOT_FOUND);
        }

        if($user->getId() != $id){
            return new JsonResponse(['status' => 'error', 'message' => 'You cannot delete someone else\'s account!'], Response::HTTP_FORBIDDEN);
        }

        $single_user = $entityManager->getRepository(User::class)->find($id);

        if (!$single_user) {
            return new JsonResponse(['status' => 'error', 'message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        try {
            $tokenStorage->setToken(null);
            $request->getSession()->invalidate();

            $entityManager->remove($single_user);
            $entityManager->flush();

            return new JsonResponse(['status' => 'success', 'message' => 'User deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 'error', 'message' => 'An error occurred while deleting the user'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
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
