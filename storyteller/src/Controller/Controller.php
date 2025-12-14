<?php
namespace App\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
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

    private GameManager $gameManager;
    private PlayerManager $playerManager;
    
    public function __construct(GameManager $gameManager)
    {
        $this->gameManager = $gameManager;
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



    #[Route('/game_loop', name: 'game_loop')]
    public function gameLoop(): Response
    {
        if( $gameManager === null){
            $gameManager = new GameManager();
        }   
        $this->render('first_game.html.twig',[]); //passer les params
        while (true){//tant qu'on est pas à la fin de la partie
            $this->render('round_game.html.twig',[]);//passer les params
            //recup les inputs
            //attendre les inputs de tous (loop d'1sec)
            //enregistrer sur neo4j
        }
        return $this->render('end_game.html.twig',[]); //passer les params
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
        if( $gameManager === null){
            $gameManager = new GameManager();
        }   
        $client = ClientBuilder::create()
            ->withDriver('default', 'bolt://neo4j:password@neo4j:7687')
            ->build();
        $result = $client->run('RETURN 1 AS test');
        $value = $result->first()->get('test');

        return new Response("Connexion OK : $value");
    }

    #[Route('/start_game', name: 'start_game')]
    public function startGame(): Response
    {
        if( $gameManager === null){
            $gameManager = new GameManager();
        }   
        //TODO only host can start
        //TODO set game started in redis
        return null; //passer les params
    }
}