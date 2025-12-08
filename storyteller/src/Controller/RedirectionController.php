<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RedirectionController extends AbstractController
{
    #[Route('/create-game', name: 'create_game')]
    public function createGame(): Response
    {
        return $this->render('default/create_game.html.twig');
    }

    #[Route('/join-game', name: 'join_game')]
    public function joinGame(): Response
    {
        return $this->render('default/join_game.html.twig');
    }
}
