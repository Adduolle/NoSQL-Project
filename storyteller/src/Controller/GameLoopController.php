<?php
namespace App\Controller;

use App\Service\GameManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GameLoopController extends AbstractController
{
    private GameManager $gm;

    #[Route('/start_game', name: 'start_game_loop')]
    public function gameLoop(): Response
    {
        $this->gm->createGame();
        $this->render('first_game.html.twig');
        while (true){
            //TODO for each round
            $this->render('round_game.html.twig');
            //TODO get every player's input
            //TODO check if everyone sent
            //TODO register inputs on Neo4J
            //TODO generate next game views
        }
        //TODO generate end game content
        return $this->render('end_game.html.twig');
    }
}