<?php

namespace App\Service;

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
        $this->redis->lPush("$key:players", $idUser);

        return $randomCode;
    }

    // Ajouter un joueur à une party
    public function addPartyUser(string $idParty, string $idUser): void
    {
        $this->redis->lPush("party:$idParty:players", $idUser);
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

    public function getRound(string $idUser, int $round): ?string
    {
        return $this->redis->lIndex("rounds:$idUser", $round) ?: null;
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
}
