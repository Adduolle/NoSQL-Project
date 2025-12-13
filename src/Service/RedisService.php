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

    // Tu peux rajouter autant de méthodes que tu veux :
    public function increment(string $key): int
    {
        return $this->redis->incr($key);
    }
}
