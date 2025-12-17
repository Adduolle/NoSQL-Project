<?php

namespace App\Service;
use App\Service\RedisService;

class RequetesRedis
{
    private RedisService $redis;

    public function __construct()
    {
        $this->redis = new RedisService();
    }

    // Création d'une party avec type et premier joueur
    public function createParty(string $typeParty, string $idUser): string
    {
        do {
            $randomCode = str_pad((string)random_int(0, 99999), 5, '0', STR_PAD_LEFT);
            $key = "party:$randomCode";
        } while ($this->redis->get($key) !== false);

        $this->redis->hSet($key, 'type', $typeParty);
        $this->redis->hSet($key, 'actual_round','0');
        $this->redis->hSet($key, 'started', '0');
        $this->redis->lPush("$key:players", $idUser);

        return $randomCode;
    }

    public function startGame(string $roomId):void{
        $this->redis->hSet("party:$roomId", 'started', '1');
        $players = $this->getPartyUsers($roomId);
        foreach ($players as $player){
            $p = json_decode($player,true);
            $this->redis->hSet("party:$roomId:round_status", $p['id'], '0');
        }
    }

    public function validRound(string $userId, string $gameId):void{
        $this->redis->hSet("party:$gameId:round_status",$userId, '1');
    }

    public function haveAllPlayersPlayed(string $gameId): bool {
        $roundStatus = $this->redis->hGetAll("party:$gameId:round_status");
        foreach ($roundStatus as $status) {
            $s = json_decode($status,true);
            if ($s !== 1) {
                return false;
            }
        }
        return true;
    }

    public function incrRoundCounter(string $gameId): int
    {
        return $this->redis->incr("party:$gameId:round_counter");
    }

    public function resetRound(string $gameId): void
    {
        $this->redis->set("party:$gameId:round_counter", 0);
        $this->setPlayedBackToZero($gameId);
    }


    public function setPlayedBackToZero(string $gameId): void {
        $players = $this->getPartyUsers($gameId);
        foreach ($players as $player) {
            $p = json_decode($player,true);
            $this->redis->hSet("party:$gameId:round_status",$p['id'], '0');
        }
    }


    public function getGameStatus(string $roomId):bool{
        return $this->redis->hGet("party:$roomId", 'started') === '1';
    }

    public function getRoomType(string $roomId):string{
        return $this->redis->hGet("party:$roomId",'type');
    }

    public function addPartyUser(string $idParty, string $User): void
    {
        $key = "party:$idParty:players";

        $players = $this->GetPartyUsers($idParty);

        foreach ($players as $p) {
            $existing = json_decode($p, true);
            if ($existing['id'] === json_decode($User, true)['id']) {
                return; 
            }
        }

        $this->redis->rPush($key, $User);
    }


    // Récupérer tous les joueurs d'une party
    public function getPartyUsers(string $idParty): array
    {
        return $this->redis->lRange("party:$idParty:players", 0, -1) ?: [];
    }

    // Récupérer les infos de la party (type etc.)
    public function getPartyInfo(string $idParty): array
    {
        return $this->redis->hGetAll("party:$idParty");
    }

    public function getPartyPlayers(string $idParty): array
    {
       return $party['players'] = $this->redis->lRange("party:$idParty:players", 0, -1);
    }

    public function getPartyType(string $idParty): ?string
    {
        return $this->redis->hGet("party:$idParty", 'type');
    }

    public function deleteParty(string $idParty): void
    {
        $this->redis->delete("party:$idParty");
        $this->redis->delete("party:$idParty:players");
    }


    public function getNickname(string $userId, string $idParty):string
    {
        $players = $this->getPartyPlayers($idParty);
        foreach ($players as $player){
            $p = json_decode($player, true);
            if ($p['id'] === $userId) {
                return $p['username'];
            }
        }
        return 'no username found';
    }
}
