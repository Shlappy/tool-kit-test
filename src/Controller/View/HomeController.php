<?php declare(strict_types=1);

namespace App\Controller\View;

use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends BaseController
{
    /**
     * Главная страница
     */
    #[Route(path: '/', name: 'home', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('views/home.html.twig');
    }
}