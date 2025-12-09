<?php
namespace App\Service;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\HttpFoundation\Request;

class GameManager
{
    public function getNextRoundId(string $gameId, int $roundNumber, string $playerId): string
    {
        // TODO: use redis
        return '';
    }

    public function createGame(string $gameId, array $playerIds): void
    {
        // TODO: create party with list of player in redis
        // TODO: use neo4j
        $this->createGameWithParticipants($gameId, $playerIds);
        
        for ($k = 0; $k < count($playerIds); $k++) {
            // createIdHistoire
            $storyId = ''; // TODO: générer un ID unique pour l'histoire
            $this->createStory($gameId, $storyId);
            
            for ($j = 0; $j < count($playerIds); $j++) {
                $scriptId = ''; // TODO: générer un ID unique pour le script
                $this->createScript($storyId, $scriptId, $j);
            }
        }
        
        // TODO: create the list of script in order
    }

    private function createGameWithParticipants(string $gameId, array $playerIds): void
    {
        // TODO: implémenter la création du jeu avec les participants
    }

    private function createStory(string $gameId, string $storyId): void
    {
        // TODO: implémenter la création de l'histoire
    }

    private function createScript(string $storyId, string $scriptId, int $index): void
    {
        // TODO: implémenter la création du script
    }
}