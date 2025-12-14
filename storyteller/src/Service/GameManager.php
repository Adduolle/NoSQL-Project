<?php

namespace App\Service;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


class GameManager
{
    private RequetesRedis $requetesRedis;
    private RequetesNeo4j $requetesNeo4j;

    public function __construct()
    {
        $this->requetesRedis = new RequetesRedis();
        $this->requetesNeo4j = new RequetesNeo4j();
    }

    //public function getNextRoundId(string $gameId, int $RoundNumber,string $playerId): string
    //{
        //todo use redis
    //}

    public function createGame(string $gameId, array $playerIds): void
    {
        //todo create party with list of player in redis

        //todo use neo4j
        //createGameWithParticipants($gameId, $playerIds);
        //for k in range(len(playerIds)):
            //createIdHistoire
          //  createStory($gameId , $storyId)
            //for j in range(len(playerIds)):
              //  createScript($storyId,$scriptId, j)

        //todo create the list of script in order 
    }

    public function setRoomType(string $roomType, SessionInterface $session): array
    {
        // Créer un userId unique pour l'hôte
        if (!$session->has('userID')) {
            $userId = bin2hex(random_bytes(8));
            $pseudo = 'Host' . random_int(1000, 9999);

            $session->set('userID', $userId);
            $session->set('pseudo', $pseudo);
        } else {
            $userId = $session->get('userID');
            $pseudo = $session->get('pseudo');
        }

        // Créer la salle et ajouter l'hôte comme premier utilisateur
        $roomId = $this->requetesRedis->createParty($roomType, json_encode([
            'id' => $userId,
            'username' => $pseudo
        ]));

        return [
            'roomType' => $roomType,
            'roomId' => $roomId,
            'userID' => $userId,
            'pseudo' => $pseudo
        ];
    }
    public function joinRoom(string $roomId, SessionInterface $session): array
    {
        // Créer un userId unique pour le joueur
        if (!$session->has('userID')) {
            $userId = bin2hex(random_bytes(8));
            $pseudo = 'Player' . random_int(1000, 9999);

            $session->set('userID', $userId);
            $session->set('pseudo', $pseudo);
        } else {
            $userId = $session->get('userID');
            $pseudo = $session->get('pseudo');
        }

        // Ajouter le joueur à la salle existante
        $this->requetesRedis->addPartyUser($roomId, json_encode([
            'id' => $userId,
            'username' => $pseudo
        ]));

        $roomType = $this->requetesRedis->GetPartyType($roomId);

        return [
            'roomType' => $roomType,
            'roomId' => $roomId,
            'userID' => $userId,
            'pseudo' => $pseudo
        ];
    }

    public function getPlayersInGame(string $roomId): array
    {
        $usersData = $this->requetesRedis->GetPartyUsers($roomId);
        $players = [];

        foreach ($usersData as $userData) {
            $user = json_decode($userData, true);
            if ($user) {
                $players[] = [
                    'id' => $user['id'],
                    'username' => $user['username']
                ];
            }
        }

        return $players;
    }
}
