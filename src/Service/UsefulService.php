<?php 


namespace App\Service;

use App\Entity\User;
use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;

class UsefulService {

    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }

    public function getDataWithCompletion($project)
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