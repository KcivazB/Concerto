<?php

namespace App\Controller;

use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(EntityManagerInterface $entityManager)
    {
        $user = $this->getUser();
        if ($user) {
            $projects = $user->getProjects();
            $collabs = $entityManager->getRepository(Project::class)->findUserCollabs($user->getId());
        } else {
            $projects = [];
            $collabs = [];
        }

        $projects = $this->getDataWithCompletion($projects);
        $collabs = $this->getDataWithCompletion($collabs);

        return $this->render('home/index.html.twig', [
            'projects' => $projects,
            'collabs' => $collabs
        ]);
    }

    private function getDataWithCompletion($projects)
    {
        foreach ($projects as $project) {
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
        }

        return $projects;
    }
}
