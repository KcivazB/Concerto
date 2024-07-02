<?php 

namespace App\Controller;

use App\Entity\Idea;
use App\Form\IdeaType;
use App\Entity\Project;
use App\Form\ProjectType;
use App\Entity\Invitation;
use Symfony\Component\Mime\Email;
use App\Repository\IdeaRepository;
use App\Repository\UserRepository;
use App\Repository\StatusRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\CollaboratorInvitationFormType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class ProjectController extends AbstractController
{
    /**
     * @Route("/projects/new", name="project_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $project = new Project();

        // Create a form to handle the submission
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $project->setOwner($user);
            $project->setStartDate(new \DateTime());

            $entityManager->persist($project);
            $entityManager->flush();

            // Redirect back to the project view or handle as needed
            return $this->redirectToRoute('project_view', ['id' => $project->getId()]);
        }

        return $this->render('project/_form.html.twig', [
            'project' => $project,
            'form' => $form->createView(),
            'form_action' => $this->generateUrl('project_new')
        ]);
    }

    /**
     * @Route("/projects/{id}", name="project_view", methods={"GET"})
     */
    public function view($id, Request $request, ProjectRepository $projectRepository, Security $security): Response
    {
        // Retrieve the project by ID
        $project = $projectRepository->find($id);

        // Check if the project exists
        if (!$project) {
            throw $this->createNotFoundException('Project not found');
        }

        $user = $security->getUser();

        // Check if the current user is either the owner or a contributor
        $isOwner = $project->getOwner() === $user;
        $isContributor = $project->getContributors()->contains($user);

        // If the user is neither the owner nor a contributor, deny access
        if (!$isOwner && !$isContributor) {
            throw $this->createAccessDeniedException('You are not authorized to view this project');
        }
        
        $project = $this->getDataWithCompletion($project);

        // Render the project view template
        return $this->render('project/view.html.twig', [
            'project' => $project,
        ]);
    }
    /**
     * @Route("/projects/{id}", name="project_delete", methods={"DELETE"})
     */
    public function delete($id, Request $request,  EntityManagerInterface $entityManager): Response
    {
        $project = $entityManager->getRepository(Project::class)->find($id);
        if (!$project) {
            return new JsonResponse(['error' => 'Idea not found'], Response::HTTP_NOT_FOUND);
        }

        $project->setIsDelete(true);
        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }
    
    /**
     * @Route("/projects/{id}/edit", name="project_edit", methods={"GET", "POST"})
     */
    public function edit($id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $project = $entityManager->getRepository(Project::class)->find($id);
        
        // Handle the case where the project is not found
        if (!$project) {
            throw $this->createNotFoundException('The project does not exist');
        }
        
        if ($project->getOwner() != $this->getUser()) {
            throw $this->createNotFoundException('Only owner can edit project data');
        }

        // Create a form to handle the submission
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // Redirect back to the project view or handle as needed
            return $this->redirectToRoute('project_view', ['id' => $project->getId()]);
        }

        return $this->render('project/_form.html.twig', [
            'project' => $project,
            'form' => $form->createView(),
            'form_action' => $this->generateUrl('project_edit', ['id' => $project->getId()])
        ]);
    }

    /**
     * @Route("/projects/{id}/addIdea", name="project_add_idea", methods={"GET", "POST"})
     */
    public function addIdea($id, Request $request, ProjectRepository $projectRepository, StatusRepository $statusRepository): Response
    {
        $status_name = $request->get('status');
        $status = $statusRepository->findOneBy(['name' => $status_name]);
        // just set up a fresh $task object (remove the example data)
        $idea = new Idea();
        $project = $projectRepository->find($id);
        $idea->setProject($project);
        $idea->setStartDate(new \DateTime());
        $idea->setEndDate(null);
        $idea->setStatus($status);
        $form = $this->createForm(IdeaType::class, $idea);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $idea = $form->getData();

            // Get the currently authenticated user
            $user = $this->getUser();
            $idea->setOwner($user); // Set the user as the owner of the idea

            // Persist the idea
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($idea);
            $entityManager->flush();

            return $this->redirectToRoute(
                'project_view',
                [
                    "id" => $project->getId() 
                ]
             );
        }

        return $this->render('idea/_form.html.twig', [
            'form' => $form->createView(), // Passer l'objet FormView à la vue
            'project' => $project,
            'form_action' => $this->generateUrl('project_add_idea', ['id' => $project->getId()])

        ]);
    }

    /**
     * @Route("/projects/{id}/invite", name="project_invite_collaborator", methods={"GET", "POST"})
     */
    public function inviteCollaborator(
        $id,
        Request $request,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        UserRepository $userRepository,
        UrlGeneratorInterface $urlGenerator
    ) {
        $project = $entityManager->getRepository(Project::class)->find($id);

        $form = $this->createForm(CollaboratorInvitationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $email = $data['email'];
            $user = $userRepository->findOneBy(['email' => $email]);
            $token = bin2hex(random_bytes(32));

            if ($user) {
                // User exists, send an invitation email
                $invitation = new Invitation();
                $invitation->setEmail($email);
                $invitation->setToken($token);
                $invitation->setCreatedAt(new \DateTimeImmutable());
                $invitation->setProject($project);

                $entityManager->persist($invitation);
                $entityManager->flush();

                $invitationLink = $urlGenerator->generate('accept_invitation', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

                $email = (new TemplatedEmail())
                    ->from('concerto@virgilebaisnee.fr')
                    ->to($email)
                    ->subject('Invitation to join project ' . $project->getTitle())
                    ->htmlTemplate('emails/invitation_email.html.twig')
                    ->context([
                        'projectTitle' => $project->getTitle(),
                        'invitationLink' => $invitationLink,
                    ]);

                try {
                    $mailer->send($email);
                } catch (TransportExceptionInterface $e) {
                    // Handle email sending failure
                    dd($e); // Replace with appropriate error handling
                }
            } else {
                // User does not exist, create invitation and redirect to registration form
                $invitation = new Invitation();
                $invitation->setEmail($email);
                $invitation->setToken($token);
                $invitation->setCreatedAt(new \DateTimeImmutable());
                $invitation->setProject($project);

                $entityManager->persist($invitation);
                $entityManager->flush();

                return $this->redirectToRoute('user_registration', ['token' => $token]);
            }

            return $this->redirectToRoute('project_view', ['id'=> $project->getId()]);
        }

        return $this->render('project/_add_member_form.html.twig', [
            'project' => $project,
            'form' => $form->createView(),
            'form_action' => $this->generateUrl('project_invite_collaborator', ['id' => $project->getId()])
        ]);
    }

    
    private function initializeIdeas($ideas)
    {
        foreach ($ideas as $idea) {
            $idea->getComments()->initialize(); // Force initialization of comments
        }
    }

    private function getDataWithCompletion($project)
    {
        
        $tasks = $project->getIdeas(); // Assuming tasks are fetched through a method like getTasks()

        if ($tasks->isEmpty()) {
            $project->completion = 0;
        } else {
            $completedTasks = $tasks->filter(function ($task) {
                return $task->getStatus()->getName() === 'done'; // Adjust 'done' according to your task status logic
            });

            $completedCount = count($completedTasks);
            $totalCount = count($tasks);
            $completionPercentage = ($totalCount > 0) ? ($completedCount / $totalCount) * 100 : 0;

            $project->completion = round($completionPercentage, 2); // Rounded to two decimal places
        }

        return $project;
    }


}
