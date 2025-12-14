<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use App\Service\requetesNeo4j;

class PlayerManager
{
    public function checkOrCreatePlayers(Request $request, requetesNeo4j $nj4): void
    {
        // On gère le joueur actuel
        if (!$request->cookies->get('player_0')){
            $this->createTestPlayer('0');
        };
        // On gère les 3 joueurs test
        if (!$request->cookies->get('player_1')){
            for ($i = 1; $i <= 3; $i++) {
                $this->createTestPlayer((string)$i);
            }
        }
    }
    public function createTestPlayer(string $playerId):Cookie{
        $cookie = Cookie::create("player_".$playerId)
            ->withValue(json_encode([
                'in_game'=>true,
                'game_id'=>'game_0',
                'round'=>0,
                'nickname'=>'Joueur '.$playerId,
            ]))
            ->withExpires(new \DateTime('+1 day')) // cookie valide 1 jour
            ->withPath('/')
            ->withHttpOnly(false);
        return $cookie;
    }

    public function getNickname(Request $request, string $userId): ?string
    {
        $playerCookie = $request->cookies->get('player_'.$userId);
        if ($playerCookie){
            return $playerCookie['nickname'];
        } else {
            return "No Nickname Found";
        }
        ;
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
        $token = $request->cookies->get($userId);
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
    
}
