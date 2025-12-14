<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Laudis\Neo4j\ClientBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\GameManager;
use App\Service\PlayerManager;
use App\Service\requetesNeo4j;

class Controller extends AbstractController
{

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

    //join game button, sends a password code
    #[Route('/join_game', name: 'join_game', methods: ['POST'])]
    public function joinGame(Request $request): Response
    {
        $gameCode = $request->request->get('gameCode');
        // implémenter le if de vérification du code
        //si bon
        //return $this->render('join_game.html.twig',[]);
        //si faux
        $this->addFlash('error','Le code ne correspond à aucune partie.');
        return $this->redirectToRoute('homepage');
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

    #[Route('/game_loop', name: 'game_loop', methods: ['POST','GET'])]
    public function gameLoop(Request $request, GameManager $gm, PlayerManager $pm, requetesNeo4j $nj4): Response
    {
        // On récup le joueur actuel
        $userId = $this->getUser()?->getUserIdentifier() ?? null;
        $player = $request->cookies->get('player_'.$userId);
        $playerValues = json_decode($player, true);
        // On récup la game liée au joueur actuel
        $gameId = $player ? $player['game_id'] : null;
        $game=$gm->getGameById($gameId, $request);

        // Vérifier si la requête est POST
        if ($request->isMethod('POST')) {
            // Créer un temps de chargement qui attend les réponses POST des autres joueurs

            // Incrémenter le round de la game
            $game['round']=$game['round']+1;
            // Récupérer la valeur envoyée
            $playerStory = $request->request->get('playerstorie');
            if (!$playerStory) {
                $response=$this->render('page-stories.html.twig', ['game'=>$game,
                    'nickname'=>$pm->getNickname($request, $userId),'assigned_text'=>'Aucun texte assigné']);
                return $response;
            }
            // Récupérer le nouveau texte selon le round et l'id du joueur
            $assignedTxt = $nj4->getAssignedTextForPlayerInRound($gameId, $userId, $game['round']);
            // Préparer la réponse
            $response =$this->render('page-stories.html.twig', ['game'=>$game,
                'nickname'=>$pm->getNickname($request, $userId),'assigned_text'=>$assignedTxt]);

            // Neo4j
            $round=$gm->getGameRound('game_0', $request)-1;
            $nj4->writeScript("game_0"."_story_".$round."_script_".$round,$userId,$playerStory);

            
            // return response
            return $response;
        }
        // Définir la vue du first round
        $response = $this->render('page-stories.html.twig', ['game'=>$game,
                'player'=>$playerValues,'assigned_text'=>'']);
        return $response;
    }
}