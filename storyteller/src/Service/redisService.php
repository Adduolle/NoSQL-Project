<?php

namespace App\Service;

use Redis;

class RedisService
{
    private Redis $redis;

    public function __construct()
    {
        // Connexion au service Docker "redis"
        $this->redis = new Redis();
        $this->redis->connect('redis', 6379);
    }

    public function set(string $key, mixed $value): void
    {
        $this->redis->set($key, $value);
    }

    public function get(string $key): mixed
    {
        return $this->redis->get($key);
    }

    public function delete(string $key): void
    {
        $this->redis->del($key);
    }

    // Ajoute un élément en tête d'une liste
    public function lPush(string $key, string $value): int
    {
        return $this->redis->lPush($key, $value);
    }
}
