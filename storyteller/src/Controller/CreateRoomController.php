<?php
namespace App\Controller;

use App\Service\GameManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Room;
use App\Service\PlayerManager;
use Symfony\Component\HttpFoundation\JsonResponse;

class CreateRoomController extends AbstractController
{

    private GameManager $gameManager;
    private PlayerManager $playerManager;

    #[Route('/waitroom/normal_game', name: 'create_waitroom_normal')]
    public function createNormalRoom(): Response
    {
        $room=$this->gameManager->createRoom("normal");
        return $this->render('waitroom.html.twig',[
            'roomCode'=>$room->getCode(),
            'gameType'=>'normal'
        ]);
    }

    #[Route('/waitroom/path_game', name: 'create_waitroom_path')]
    public function createPathRoom(): Response
    {
        $room=$this->gameManager->createRoom("path");
        return $this->render('waitroom.html.twig',[
            'roomID'=>$room->getId(),
            'roomCode'=>$room->getCode(),
            'gameType'=>'path',
            'userID'=>$this->playerManager->getCookieName()
        ]);
    }

    #[Route('/waitroom/{id}/players', name: 'room_players')]
    public function roomPlayers(Room $room): JsonResponse
    {
        $players = [];

        foreach ($room->getPlayers() as $player) {
            $players[] = $player->getUsername();
        }
        $host=$players[0] ?? null;

        return new JsonResponse([
            'players' => $players,
            'host' => $host
        ]);
    }

}