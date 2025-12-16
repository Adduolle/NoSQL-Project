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
        return $this->redirectToRoute('game_loop', ['round' => 0]);
    }

    #[Route('/game_loop/{round}', name: 'game_loop', methods: ['POST','GET'])]
    public function gameLoop(Request $request, SessionInterface $sessionInterface, int $round, ?string $content): Response
    {
        $userId = $sessionInterface->get('userID');
        $gameId = $sessionInterface->get('roomID');
        $nickname = $this->requetesRedis->getNickname($userId, $gameId);

        $gameRound = $round;

        if (isset($content)) {
            $this->requetesNeo4j->writeScript(
                $gameId."_story_".$gameRound."_script_".$gameRound,
                $userId,
                $content
            );

            if ($gameRound < $this->gameManager->getNbPlayers()) {
                $assignedTxt = $this->requetesNeo4j->getAssignedTextForPlayerInRound(
                    $gameId, $userId, $gameRound
                );

                $response = $this->render('page-stories.html.twig', [
                    'round' => $gameRound,
                    'nickname' => $nickname,
                    'assigned_text' => $assignedTxt,
                    'roomID' => $gameId
                ]);
            } else {
                $response = $this->render('page-stories.html.twig', [
                    'round' => $gameRound,
                    'nickname' => $nickname,
                    'assigned_text' => 'Aucun texte assigné',
                    'roomID' => $gameId
                ]);
            }

            if ($gameRound >= $this->gameManager->getNbPlayers()) {
                $resultat = $this->requetesNeo4j->getStories($gameId);
                return $this->render('resultat-histoire.html.twig', [
                    'nickname' => $nickname,
                    'stories' => $resultat,
                    'roomID' => $gameId
                ]);
            }
            return $response;
        }

        // First round si GET sans contenu
        return $this->render('page-stories.html.twig', [
            'round' => $gameRound,
            'nickname' => $nickname,
            'assigned_text' => '',
            'roomID' => $gameId
        ]);
    }


    #[Route('/game_loop/{id}/status', name: 'game_loop_status')]
    public function gameLoopStatus(string $id): JsonResponse
    {
        $roomStatus = $this->requetesRedis->haveAllPlayersPlayed($id);

        return $this->json([
            'allPlayed' => $roomStatus,
        ]);
    }

    #[Route('/game_loop/{gameId}/{round}/recup', name: 'game_loop_recup', methods: ['POST'])]
    public function gameLoopRecup(string $gameId, int $round, Request $request): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['content'])) {
            return $this->json(['success' => false], 400);
        }

        $content = $data['content'];

        $this->requetesRedis->CreateScriptText($gameId, $content);

        return $this->json([
            'success' => true
        ]);
    }


    #[Route('/game_loop/{gameId}/validRound', name:'game_loop_validround')]
    public function validRound(string $gameId, SessionInterface $sessionInterface): Response
    {       
        $userId = $sessionInterface->get('userID');     
        $this->requetesRedis->validRound($userId,$gameId);
        return new Response();
    }
}