<?php 


namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PoliciesController extends AbstractController
{
    /**
     * @Route("/policies", name="policies")
     */
    public function policies(){
        return $this->render('policies/policies.html.twig');
    }
    /**
     * @Route("/terms", name="terms")
     */
    public function terms(){
        return $this->render('policies/terms.html.twig');
    }
}