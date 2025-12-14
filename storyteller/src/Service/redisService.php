<?php

namespace App\Service;

use Redis;

class RedisService
{
    private Redis $redis;
    private int $ttl; 

    public function __construct()
    {
        $this->redis = new Redis();
        $this->redis->connect('redis', 6379);

        $this->ttl = 3600; 
    }

    public function set(string $key, mixed $value): void
    {
        $this->redis->set($key, $value, $this->ttl);
    }

    public function get(string $key): mixed
    {
        return $this->redis->get($key);
    }

    public function delete(string $key): void
    {
        $this->redis->del($key);
    }

    public function lPush(string $key, string $value): int
    {
        $result = $this->redis->lPush($key, $value);
        $this->redis->expire($key, $this->ttl); 
        return $result;
    }

    public function lRange(string $key, int $start, int $stop): array
    {
        return $this->redis->lRange($key, $start, $stop) ?: [];
    }

    public function hSet(string $key, string $field, string $value): void
    {
        $this->redis->hSet($key, $field, $value);
    }

    public function hGet(string $key, string $field): ?string
    {
        $value = $this->redis->hGet($key, $field);
        return $value === false ? null : $value;
    }

    public function hGetAll(string $key): array
    {
        return $this->redis->hGetAll($key) ?: [];
    }


}
