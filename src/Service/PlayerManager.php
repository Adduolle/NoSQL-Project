<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Psr\Cache\CacheItemPoolInterface;

class PlayerManager
{
    private CacheItemPoolInterface $cache;
    private string $cookieName;
    private int $ttlSeconds;

    public function __construct(CacheItemPoolInterface $cache, string $cookieName = 'player_token', int $ttlSeconds = 604800)
    {
        $this->cache = $cache;
        $this->cookieName = $cookieName;
        $this->ttlSeconds = $ttlSeconds;
    }
    public function getCookieName(): string
    {
        return $this->cookieName;
    }

    public function getTtl(): int
    {
        return $this->ttlSeconds;
    }

    /**
     * Ensure we have a token and corresponding cache item, or create them.
     * Returns [token, cacheKey, CacheItemInterface item, data array]
     */
    public function loadOrCreate(Request $request, ?string $userId = null): array
    {
        $token = $request->cookies->get($this->cookieName);
        $now = new \DateTimeImmutable();

        if ($token) {
            $cacheKey = 'player_token_' . hash('sha256', $token);
            $item = $this->cache->getItem($cacheKey);
            if (!$item->isHit()) {
                $legacyKey = 'player_token_' . $token;
                $legacyItem = $this->cache->getItem($legacyKey);
                if ($legacyItem->isHit()) {
                    $data = $legacyItem->get();
                    $item->set($data);
                    $item->expiresAfter($this->ttlSeconds);
                    $this->cache->save($item);
                    try {
                        $this->cache->deleteItem($legacyKey);
                    } catch (\Throwable $e) {
                        // noop - not critical
                    }
                    $item = $this->cache->getItem($cacheKey);
                }
            }

            if ($item->isHit()) {
                $data = $item->get();
                $data['last_seen'] = $now->format(DATE_ATOM);
                $item->set($data);
                $item->expiresAfter($this->ttlSeconds);
                $this->cache->save($item);
                return [$token, $cacheKey, $item, $data];
            }
        }

        if ($userId && !$token) {
            // For authenticated users we key the cache by a stable identifier
            $storageId = 'user_' . (string) $userId;
            $cacheKey = 'player_token_' . hash('sha256', $storageId);
            $item = $this->cache->getItem($cacheKey);
            if ($item->isHit()) {
                $data = $item->get();
                // Create a token to represent this user in cookies for convenience
                $token = bin2hex(random_bytes(16));
                $data['last_seen'] = $now->format(DATE_ATOM);
                $item->set($data);
                $item->expiresAfter($this->ttlSeconds);
                $this->cache->save($item);
                return [$token, $cacheKey, $item, $data];
            }
        }

        // Create new token and cache item
        do {
            $token = bin2hex(random_bytes(16));
            $cacheKey = 'player_token_' . hash('sha256', $token);
            $item = $this->cache->getItem($cacheKey);
        } while ($item->isHit());

        $data = [
            'created_at' => $now->format(DATE_ATOM),
            'last_seen' => $now->format(DATE_ATOM),
        ];
        $item->set($data);
        $item->expiresAfter($this->ttlSeconds);
        $this->cache->save($item);

        return [$token, $cacheKey, $item, $data];
    }

    public function getNickname(array $data): ?string
    {
        return $data['username'] ?? null;
    }
    
}
