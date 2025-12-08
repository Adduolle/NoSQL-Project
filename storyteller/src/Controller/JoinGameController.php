<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class JoinGameController extends AbstractController
{
    #[Route('/join-game', name: 'join_game')]
    public function joinGame(): Response
    {
        return $this->render('join_game.html.twig');
    }
    //TODO ask code
    //TODO if good code :
        //TODO associate player cookie to game cookie
        //TODO redirect to waitroom
    //TODO if bad code:
        //TODO pop up error
}