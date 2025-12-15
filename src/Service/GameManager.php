<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class GameManager
{
    private RequetesRedis $requetesRedis;
    private int $nbPlayers=0;

    public function __construct()
    {
        $this->requetesRedis = new RequetesRedis();
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
        $session->set('roomID', $roomId);
        $session->set('round',0);
        $session->set('roomType', $roomType);

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
        
        $session->set('roomID', $roomId);
        $session->set('round',0);

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
                $this->nbPlayers+=1;
                $players[] = [
                    'id' => $user['id'],
                    'username' => $user['username']
                ];
            }
        }

        return $players;
    }

    public function getRound(SessionInterface $session):int{
        if ($session->has('round')){
            return $session->get('round');
        } else {
            $session->set('round',0);
            return 0;
        }
    }

    public function incrRound(SessionInterface $session):int{
        $session->set('round',$session->get('round')+1);
        return $session->get('round');
    }

    public function getNbPlayers():int{
        return $this->nbPlayers;
    }
}