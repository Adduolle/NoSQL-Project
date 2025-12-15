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

        // Récupérer les joueurs existants
        $players = $this->GetPartyUsers($idParty);

        // Vérifier si le joueur est déjà présent
        foreach ($players as $p) {
            $existing = json_decode($p, true);
            if ($existing['id'] === json_decode($User, true)['id']) {
                return; // déjà présent, on n'ajoute pas
            }
        }

        // Ajouter à la fin pour que l'host reste le premier
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

    // Gestion des rounds
    public function createRounds(string $idUser, array $idRounds): void
    {
        $key = "rounds:$idUser";
        $this->redis->delete($key);
        foreach ($idRounds as $round) {
            $this->redis->lPush($key, $round);
        }
    }

    public function getRound(string $idUser): ?string
    {
        return $this->redis->get("acutal_round:$idUser") ?: null;
    }

    public function getRounds(string $idUser): array
    {
        return $this->redis->lRange("rounds:$idUser", 0, -1) ?: [];
    }

    public function deleteRounds(string $idUser): void
    {
        $this->redis->delete("rounds:$idUser");
    }

    // Gestion des textes des scripts
    public function createScriptText(string $idScript, string $texte): void
    {
        $this->redis->set("script:$idScript", $texte);
    }

    public function getScriptText(string $idScript): ?string
    {
        return $this->redis->get("script:$idScript") ?: null;
    }

    public function deleteScriptText(string $idScript): void
    {
        $this->redis->delete("script:$idScript");
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
