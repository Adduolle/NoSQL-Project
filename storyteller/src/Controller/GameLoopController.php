<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GameLoopController extends AbstractController
{
    #[Route('/in_game', name: 'get_in_game')]
    public function createNormalRoom(): Response
    {
        $player_count=8; //TODO get player count from game manager
        $rounds=[]; //TODO get rounds from game manager and game type
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