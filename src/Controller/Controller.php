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
    public function gameLoop(Request $request, SessionInterface $sessionInterface, int $round): Response
    {
        $userId = $sessionInterface->get('userID');
        $gameId = $sessionInterface->get('roomID');
        $nickname = $this->requetesRedis->getNickname($userId, $gameId);

        $gameRound = $round;

        if ($round > 0){
            
            if ($round >= count($this->gameManager->getPlayersInGame($gameId))) {
                return $this->redirectToRoute('final_visual', ['gameId' => $gameId]);
            }
            else{
                $scriptId = $this->requetesNeo4j->getScriptIdForPlayerInRound($gameId, $userId, $round - 1);
                $assignedTxt = $this->requetesNeo4j->getAssignedTextForPlayerInRound($scriptId);
                $this->requetesRedis->setPlayedBackToZero($gameId);
                $response = $this->render('page-stories.html.twig', [
                    'round' => $round,
                    'nickname' => $nickname,
                    'assigned_text' => $assignedTxt,
                    'roomID' => $gameId,
                    'scriptId' => $scriptId,
                    'userID' => $userId
                ]);
            }
            return $response;
        }

        // First round si GET sans contenu
        $scriptId = $this->requetesNeo4j->getScriptIdForPlayerInRound($gameId, $userId, $round);
        return $this->render('page-stories.html.twig', [
            'round' => $round,
            'nickname' => $nickname,
            'assigned_text' => '',
            'roomID' => $gameId,
            'scriptId' => $scriptId,
            'userID' => $userId
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

    //data = {content: "texte du joueur", scriptId: "id du script"}
    #[Route('/game_loop/{gameId}/{round}/recup', name: 'game_loop_recup', methods: ['POST'])]
    public function gameLoopRecup(string $gameId, int $round, Request $request, SessionInterface $sessionInterface): JsonResponse {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['content'])) {
            return $this->json(['success' => false], 400);
        }

        $content = $data['content'];
        $scriptId = $data['scriptId'] ?? null;

        if($scriptId === null){
            return $this->json(['success' => false], 400);
        }
        
        $this->requetesNeo4j->writeScript($scriptId, $content);

        return $this->json(['success' => true]);
    }


    #[Route('/game_loop/{gameId}/validRound', name:'game_loop_validround')]
    public function validRound(string $gameId, SessionInterface $sessionInterface): Response
    {       
        $userId = $sessionInterface->get('userID');     
        $this->requetesRedis->validRound($userId,$gameId);
        return new Response();
    }

    #[Route('/final_visual/{gameId}', name:'final_visual')]
    public function final_visual(string $gameId){
        /*$players = [
            '{"id":"test_0"}',
            '{"id":"test_1"}',
            '{"id":"test_2"}',
            '{"id":"test_3"}',
        ];
        foreach ($players as $playerJson) {
            $player = json_decode($playerJson, true);
            $id = $player['id'];
            $name = $player['id'];

            $this->requetesNeo4j->createUser($id, $name);
        }
        $gameId = 'game_test_0';
        $this->requetesNeo4j->createGame($gameId, "normal");
        $this->requetesNeo4j->createStories('game_test_0',$players);
        $this->requetesNeo4j->writeScript('game_test_0_story_test_0_script_test_0','test_0','Début histoire 0');
        $this->requetesNeo4j->writeScript('game_test_0_story_test_0_script_test_1','test_1','Suite 1 histoire 0');
        $this->requetesNeo4j->writeScript('game_test_0_story_test_0_script_test_2','test_2','Suite 2 histoire 0');
        $this->requetesNeo4j->writeScript('game_test_0_story_test_0_script_test_3','test_3','Suite 3 histoire 0');

        $this->requetesNeo4j->writeScript('game_test_0_story_test_1_script_test_1','test_1','Début histoire 1');
        $this->requetesNeo4j->writeScript('game_test_0_story_test_1_script_test_2','test_2','Suite 2 histoire 1');
        $this->requetesNeo4j->writeScript('game_test_0_story_test_1_script_test_3','test_3','Suite 3 histoire 1');
        $this->requetesNeo4j->writeScript('game_test_0_story_test_1_script_test_0','test_0','Suite 0 histoire 1');

        $this->requetesNeo4j->writeScript('game_test_0_story_test_2_script_test_2','test_2','Début histoire 2');
        $this->requetesNeo4j->writeScript('game_test_0_story_test_2_script_test_3','test_3','Suite 3 histoire 2');
        $this->requetesNeo4j->writeScript('game_test_0_story_test_2_script_test_0','test_0','Suite 0 histoire 2');
        $this->requetesNeo4j->writeScript('game_test_0_story_test_2_script_test_1','test_1','Suite 1 histoire 2');

        $this->requetesNeo4j->writeScript('game_test_0_story_test_3_script_test_3','test_3','Début histoire 3');
        $this->requetesNeo4j->writeScript('game_test_0_story_test_3_script_test_0','test_0','Suite 0 histoire 3');
        $this->requetesNeo4j->writeScript('game_test_0_story_test_3_script_test_1','test_1','Suite 1 histoire 3');
        $this->requetesNeo4j->writeScript('game_test_0_story_test_3_script_test_2','test_2','Suite 2 histoire 3');*/
        
        $results = $this->requetesNeo4j->getStories($gameId)->toArray(); //remplacer par gameId !!!!!!!!!!!!!!
        $stories=[];
        foreach ($results as $record) {
            $storyId = $record->get('storyId');
            $scriptsList = $record->get('scripts');
            $scripts = [];
            foreach ($scriptsList as $scriptMap) {
                $scripts[] = $scriptMap->toArray();          // transforme CypherMap en tableau PHP
            }
            $order=0;
            foreach ($scripts as $script) {
                $stories[$storyId][$order] = [
                'scriptId' => $script['scriptId'],
                'name' => $script['playerName'],
                'text' => $script['text']
                ];
                $order+=1;
            }
        }
            
        return $this->render('resultat-histoire.html.twig', [
            'nickname'=>'osef',
            'stories'=>json_encode($stories),
        ]);
    }
}