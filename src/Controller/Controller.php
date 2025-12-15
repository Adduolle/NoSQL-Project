<?php
namespace App\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Service\GameManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\PlayerManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\RequetesNeo4j;
use App\Service\RequetesRedis;

class Controller extends AbstractController
{

    private GameManager $gameManager;
    private RequetesNeo4j $requetesNeo4j;
    private RequetesRedis $requetesRedis;
    
    public function __construct()
    {
        $this->gameManager = new GameManager();
        $this->requetesNeo4j = new requetesNeo4j();
        $this->requetesRedis = new RequetesRedis;
    }

    #[Route('/waitroom/normal_game', name: 'create_waitroom_normal')]
    public function createNormalRoom(SessionInterface $session): Response
    {
        if (!isset($this->gameManager)) {
            $this->gameManager = new GameManager();
        }

        $room = $this->gameManager->setRoomType("normal", $session);

        return $this->render('waitroom.html.twig', [
            'roomID' => $room['roomId'],
            'roomType' => $room['roomType'],
            'userID' => $room['userID'],
            'pseudo' => $room['pseudo']
        ]);
    }


    #[Route('/waitroom/path_game', name: 'create_waitroom_path')]
    public function createPathRoom(SessionInterface $session): Response
    {
        if (!isset($this->gameManager)) {
            $this->gameManager = new GameManager();
        }

        $room = $this->gameManager->setRoomType("path", $session);

        return $this->render('waitroom.html.twig', [
            'roomID' => $room['roomId'],
            'roomType' => $room['roomType'],
            'userID' => $room['userID'],
            'pseudo' => $room['pseudo']
        ]);
    }

    #[Route('/waitroom/{id}/join', name: 'JoinRoom')]
    public function JoinRoom(string $id, SessionInterface $session): Response
    {
        if (!isset($this->gameManager)) {
            $this->gameManager = new GameManager();
        }

        $room = $this->gameManager->joinRoom($id, $session);

        return $this->render('waitroom.html.twig', [
            'roomID' => $room['roomId'],
            'roomType' => $room['roomType'],
            'userID' => $room['userID'],
            'pseudo' => $room['pseudo']
        ]);
    }

    #[Route('/waitroom/{id}/status', name: 'RoomStatus')]
    public function roomStatus(string $id): JsonResponse
    {
        $roomStatus = $this->requetesRedis->getGameStatus($id);

        return $this->json([
            'started' => $roomStatus,
        ]);
    }

    #[Route('/waitroom/{id}/players', name: 'room_players')]
    public function roomPlayers(string $id): JsonResponse
    {
        if ($this->gameManager === null) {
            $this->gameManager = new GameManager();
        }
        try {
            $players = $this->gameManager->getPlayersInGame($id);
            $host = $players[0] ?? null;
            error_log(print_r($players, true));
            return new JsonResponse([
                'players' => $players,
                'host' => $host
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/start_game', name: 'start_game')]
    public function startGame(SessionInterface $session): Response
    {
        // recup l'id de la salle dans la session
        $roomId = $session->get('roomID');
        // recup la salle avec Redis
        $roomType = $this->requetesRedis->getRoomType($roomId);
        $players = $this->requetesRedis->GetPartyUsers($roomId);
        // créer les joueurs sur neo4j
        foreach ($players as $playerData) {
            $player = json_decode($playerData, true);
            $this->requetesNeo4j->createUser($player['id'], $player['username']);
        }
        // créer la game, les stories et les script sur neo4j
        $this->requetesNeo4j->createGame($roomId, $roomType, $players);
        $this->requetesNeo4j->createStories($roomId, $players);
        $this->requetesRedis->startGame($roomId);
        // lancer la route game_loop
        return $this->redirectToRoute('game_loop');
    }

    #[Route('/game_loop', name: 'game_loop', methods: ['POST','GET'])]
    public function gameLoop(Request $request, SessionInterface $sessionInterface, ?string $content): Response
    {
        // On récup le joueur actuel
        $userId = $sessionInterface->get('userID');
        // On récup le round
        $gameId = $sessionInterface->get('roomID');
        $nickname = $this->requetesRedis->getNickname($userId,$gameId);
        $gameRound=$this->gameManager->incrRound($sessionInterface)-1;

        if (isset($content)){
            // Neo4j
            $this->requetesNeo4j->writeScript($gameId."_story_".$gameRound."_script_".$gameRound,$userId,$content);

            if (!$gameRound>=$this->gameManager->getNbPlayers()){
                // Récupérer le nouveau texte selon le round et l'id du joueur
                $assignedTxt = $this->requetesNeo4j->getAssignedTextForPlayerInRound($gameId, $userId, $gameRound);
                // Préparer la réponse
                $response =$this->render('page-stories.html.twig', ['round'=>$gameRound,
                    'nickname'=>$nickname,'assigned_text'=>$assignedTxt, 'roomID'=>$gameId]);
            } else {
                $response=$this->render('page-stories.html.twig', ['round'=>$gameRound,
                    'nickname'=>$nickname,'assigned_text'=>'Aucun texte assigné', 'roomID'=>$gameId]);
                return $response;
            }
            // Logique de endgame
            if ($gameRound>=$this->gameManager->getNbPlayers()){
                $resultat=$this->requetesNeo4j->getStories($gameId);
                $response =$this->render('resultat-histoire.html.twig', [
                    'nickname'=>$nickname,'stories'=>$resultat, 'roomID'=>$gameId]);
                return $response;
            }
            return $response;
        }

        // Définir la vue du first round
        $response = $this->render('page-stories.html.twig', ['round'=>'0',
                'nickname'=>$nickname,'assigned_text'=>'', 'roomID'=>$gameId]);
        return $response;
    }

    #[Route('/game_loop/{id}/status', name: 'game_loop_status')]
    public function gameLoopStatus(string $id): JsonResponse
    {
        $roomStatus = $this->requetesRedis->haveAllPlayersPlayed($id);

        return $this->json([
            'allPlayed' => $roomStatus,
        ]);
    }

    #[Route('/game_loop/{gameId}/{content}/recup', name:'game_loop_recup')]
    public function gameLoopRecup(string $gameId, string $content): JsonResponse
    {            
        $this->requetesRedis->setPlayedBackToZero($gameId);

        // Retourne un JSON indiquant que l'action est faite
        return $this->json([
            'success' => true,
            'gameId' => $gameId,
            'content' => $content,
        ]);
    }

    #[Route('/game_loop/{gameId}/validRound', name:'game_loop_recup')]
    public function validRound(string $gameId, SessionInterface $sessionInterface): void
    {       
        $userId = $sessionInterface->get('userID');     
        $this->requetesRedis->validRound($userId,$gameId);
    }
}