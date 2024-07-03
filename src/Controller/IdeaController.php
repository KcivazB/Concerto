<?php

namespace App\Controller;

use App\Entity\Idea;
use App\Entity\Status;
use App\Form\IdeaType;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\IdeaRepository;
use App\Service\UsefulService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class IdeaController extends AbstractController
{
    /**
     * @Route("/idea/{id}/edit", name="idea_edit")
     */
    public function edit($id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $idea = $entityManager->getRepository(Idea::class)->find($id);

        // Handle the case where the project is not found
        if (!$idea) {
            throw $this->createNotFoundException('The project does not exist');
        }
        $project = $idea->getProject();

        // Create a form to handle the submission
        $form = $this->createForm(IdeaType::class, $idea);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // Redirect back to the project view or handle as needed
            return $this->redirectToRoute('project_view', ['id' => $project->getId()]);
        }

        return $this->render('idea/_form.html.twig', [
            'project' => $project,
            'form' => $form->createView(),
            'form_action' => $this->generateUrl('idea_edit', ['id' => $idea->getId()]),
            'idea' => $idea 
        ]);
    }

    /**
     * @Route("/idea/{id}", name="idea_delete", methods={"DELETE"})
     */
    public function delete($id, Request $request,  EntityManagerInterface $entityManager)
    {
        $idea = $entityManager->getRepository(Idea::class)->find($id);
        if (!$idea) {
            return new JsonResponse(['error' => 'Idea not found'], Response::HTTP_NOT_FOUND);
        }

        $idea->setIsDelete(true);
        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }

    /**
     * @Route("/idea/{id}/comments", name="idea_comments", methods={"GET"})
     */
    public function getIdeaComments($id, IdeaRepository $ideaRepository, Request $request): Response
    {
        $idea = $ideaRepository->find($id);

        // Create a new Comment entity and form
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);

        return $this->render('project/_idea_comments_modal.html.twig', [
            'idea' => $idea,
            'form' => $form->createView(), // Pass the form view to the template
        ]);
    }

    /**
     * @Route("/idea/{id}/comment/add", name="comment_add", methods={"POST"})
     */
    public function addComment($id, Request $request, IdeaRepository $ideaRepository, EntityManagerInterface $entityManager): Response
    {
        $idea = $ideaRepository->find($id);

        // Create a new Comment entity
        $comment = new Comment();
        $comment->setPostDate(new \DateTime());
        $comment->setIdea($idea);
        $comment->setOwner($this->getUser()); // Assuming you have user authentication

        // Create a form to handle the submission
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        // Handle form submission
        if ($form->isSubmitted() && $form->isValid()) {
            // Fetch the parent comment ID from the request
            $parentCommentId = $request->request->get('comment')['parentComment'];

            // Set the parent comment (if provided)
            if ($parentCommentId) {
                $parentComment = $entityManager->getRepository(Comment::class)->find($parentCommentId);
                if ($parentComment) {
                    $comment->setParentComment($parentComment);
                }
            }

            $entityManager->persist($comment);
            $entityManager->flush();

            // Redirect back to the idea view or handle as needed
            return $this->redirectToRoute('project_view', ['id' => $idea->getProject()->getId()]);
        }

        // Handle form errors or return appropriate response
        // For simplicity, return a JSON response or render a template
        return new Response('Error handling comment submission.', Response::HTTP_BAD_REQUEST);
    }

    /**
 * @Route("/update-idea-status", name="update_idea_status", methods={"POST"})
 */
public function updateIdeaStatus(Request $request, EntityManagerInterface $entityManager, UsefulService $usefulService): Response
{
    $ideaId = $request->request->get('ideaId');
    $newStatus = $request->request->get('newStatus');

    // Fetch the Idea entity
    $idea = $entityManager->getRepository(Idea::class)->find($ideaId);
    if (!$idea) {
        return new Response('Idea not found.', Response::HTTP_NOT_FOUND);
    }

    // Fetch the Status entity
    $status = $entityManager->getRepository(Status::class)->findOneBy(['name' => $newStatus]);
    if (!$status) {
        return new Response('Status not found.', Response::HTTP_NOT_FOUND);
    }

    // Update idea status
    $idea->setStatus($status);
    $entityManager->flush();

    $project = $usefulService->getDataWithCompletion($idea->getProject());

    // Render the project container template
    return $this->render('project/_project_container.html.twig', [
        'project' => $project,
    ]);
}


}
