<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GuideDashboardController extends AbstractController
{
    /**
     * @Route("/guide/dashboard", name="guide_dashboard")
     */
    public function index(): Response
    {
        return $this->render('guide/dashboard.html.twig');
    }
} 