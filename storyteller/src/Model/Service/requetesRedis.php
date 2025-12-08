<?php

namespace App\Service;

class RequeteRedis
{
    private RedisService $redis;

    public function __construct(RedisService $redis)
    {
        $this->redis = $redis;
    }

    /**
     * Crée une paire : id -> liste d'IDs
     */
    public function createParty(string $id, array $items): void
    {
        $key = "party:$id";

        // On supprime la liste si elle existe déjà
        $this->redis->delete($key);

        // On stocke chaque élément dans une liste Redis
        foreach ($items as $item) {
            $this->redis->lPush($key, $item);
        }
    }

    /**
     * Ajoute un élément à la liste id -> liste d'IDs
     */
    public function addItem(string $id, string $item): void
    {
        $key = "party:$id";
        $this->redis->lPush($key, $item);
    }

    /**
     * Récupère la liste complète
     */
    public function getParty(string $id): array
    {
        $key = "party:$id";

        // Redis range start=0, end=-1 = toute la liste
        return $this->redis->lRange($key, 0, -1) ?: [];
    }

    /**
     * Supprime la liste
     */
    public function deleteParty(string $id): void
    {
        $key = "party:$id";
        $this->redis->delete($key);
    }
}
