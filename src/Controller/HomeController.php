<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $appSecret = $_SERVER['APP_SECRET'] ?? null;
$appCSRFPAGE = $_SERVER['APP_CSRF_PAGE'] ?? null;
$appCSRFKEYGEN = $_SERVER['APP_CSRF_KEY_GENERATE'] ?? null;

// Check if any of the variables are null or empty in one concise condition
if (empty($appSecret) || empty($appCSRFPAGE) || empty($appCSRFKEYGEN)) {
    return $this->redirectToRoute('app_setup_index');
}


        return $this->render('home/index.html.twig');
    }
}
