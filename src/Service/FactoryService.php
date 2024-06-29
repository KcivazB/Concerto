<?php 


namespace App\Service;

use App\Entity\User;
use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;

class FactoryService {

    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }

    public function generateUser($username, $avatarUrl = null){
        $randomPassword = sha1(random_bytes(18));
                    
        // User does not exist, create a new User entity
        $user = new User();
        $user->setUsername($username);
        $user->setAvatar($avatarUrl);
        $user->setPassword($randomPassword);
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function generateProject($user){
        $newProject = new Project();
        $newProject
            ->setTitle("My first Project")
            ->setDescription("This is a simple description of your first project")
            ->setOwner($user)
            ->setStartDate(new \DateTime());
        
        $this->entityManager->persist($newProject);
        $this->entityManager->flush();

        return $newProject;
    }



}