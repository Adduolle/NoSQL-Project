<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CreateGameController extends AbstractController
{
    #[Route('/create-game/normal', name: 'create_game')]
    public function createGameNormal(): Response
    {
        return $this->render('create_game.html.twig');
    }

    #[Route('/create-game/path', name: 'create_game')]
    public function createGamePath(): Response
    {
        return $this->render('create_game.html.twig');
    }
    //TODO generate room based on game cookie and type
    //TODO create 20max players
    //TODO generate game code
    //TODO redirect to waitroom
}