<?php
namespace App\Controller;

use App\Service\GameManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\PlayerManager;
use Laudis\Neo4j\ClientBuilder;
use Laudis\Neo4j\Contracts\ClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class Controller extends AbstractController
{

    private GameManager $gameManager; //eux ils sont jamais générés nan ?
    private PlayerManager $playerManager;

    #[Route('/waitroom/normal_game', name: 'create_waitroom_normal')]
    public function createNormalRoom(): Response
    {
        $room=$this->gameManager->setRoomType("normal");
        return $this->render('waitroom.html.twig',[]); //passer params
    }

    #[Route('/waitroom/path_game', name: 'create_waitroom_path')]
    public function createPathRoom(): Response
    {
        $room=$this->gameManager->setRoomType("path");
        return $this->render('waitroom.html.twig',[]); //passer params
    }

    #[Route('/waitroom/{id}/players', name: 'room_players')]
    public function roomPlayers(): JsonResponse
    {
        $players = [];

        foreach ($this->gameManager->getPlayers() as $player) {
            $players[] = $player->getUsername();
        }
        $host=$players[0] ?? null;

        return new JsonResponse([
            'players' => $players,
            'host' => $host
        ]);
    }

    #[Route('/game_loop', name: 'game_loop')]
    public function gameLoop(): Response
    {
        $this->render('first_game.html.twig',[]); //passer les params
        while (true){//tant qu'on est pas à la fin de la partie
            $this->render('round_game.html.twig',[]);//passer les params
            //recup les inputs
            //attendre les inputs de tous (loop d'1sec)
            //enregistrer sur neo4j
        }
        return $this->render('end_game.html.twig',[]); //passer les params
    }

    //join game button, sends a password code
    #[Route('/join_game', name: 'join_game')]
    public function joinGame(): Response
    {
        //on check le code passe en param
        //si bon
        return $this->render('join_game.html.twig',[]);
        //si faux
    }
    //TODO ask code
    //TODO if good code :
        //TODO associate player cookie to game cookie
        //TODO redirect to waitroom
    //TODO if bad code:
        //TODO pop up error

    #[Route('/neo4j-test', name: 'test-neo4j')]
    public function index(): Response
    {
        $client = ClientBuilder::create()
            ->withDriver('default', 'bolt://neo4j:password@neo4j:7687')
            ->build();
        $result = $client->run('RETURN 1 AS test');
        $value = $result->first()->get('test');

        return new Response("Connexion OK : $value");
    }
}