<?php

namespace App\Service;

class RequeteRedis
{
    private RedisService $redis;

    public function __construct(RedisService $redis)
    {
        $this->redis = $redis;
    }

    #gestion de party/waiting room
    public function createParty(string $idUser): void
    {
        $key = "party:$idUser";
        $this->redis->delete($key);
        $items = []; 
        foreach ($items as $item) {
            $this->redis->lPush($key, $item);
        }
    }

    public function AddPartyUser(string $IdParty, string $idUser): void
    {
        $key = "party:$IdParty";
        $this->redis->lPush($key, $idUser);
    }

    public function GetPartyUsers(string $IdParty): array
    {
        $key = "party:$IdParty"; // Fixed variable name from $id to $IdParty

        // Redis range start=0, end=-1 = toute la liste
        return $this->redis->lRange($key, 0, -1) ?: [];
    }

    public function deleteParty(string $id): void
    {
        $key = "party:$id";
        $this->redis->delete($key);
    }

    #gestion des manches
    public function CreateRounds(string $idUser, array $IdRounds): void
    {
        $key = "rounds:$idUser";
        $this->redis->delete($key);
        foreach ($IdRounds as $round) {
            $this->redis->lPush($key, $round);
        }
    }

    public function GetRound(string $idUser, int $round): ?string
    {
        $key = "rounds:$idUser";
        return $this->redis->lIndex($key, $round) ?: null;
    }

    public function GetRounds(string $idUser): array
    {
        $key = "rounds:$idUser";
        return $this->redis->lRange($key, 0, -1) ?: [];
    }

    public function DeleteRounds(string $idUser): void
    {
        $key = "rounds:$idUser";
        $this->redis->delete($key);
    }

    #gestion des textes des manches précédentes
    public function CreateScriptText(string $idScript, string $texte): void
    {
        $key = "script:$idScript";
        $this->redis->set($key, $texte);
    }

    public function GetScriptText(string $idScript): ?string
    {
        $key = "script:$idScript";
        return $this->redis->get($key) ?: null;
    }

    public function DeleteScriptText(string $idScript): void
    {
        $key = "script:$idScript";
        $this->redis->delete($key);
    }
}
