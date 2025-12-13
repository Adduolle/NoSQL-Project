<?php

namespace App\Service;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\HttpFoundation\Request;

class GameManager
{
    private RequetesRedis $requetesRedis;
    private RequetesNeo4j $requetesNeo4j;

    public function __construct()
    {
        $this->requetesRedis = new RequetesRedis();
        $this->requetesNeo4j = new RequetesNeo4j();
    }

    //public function getNextRoundId(string $gameId, int $RoundNumber,string $playerId): string
    //{
        //todo use redis
    //}

    public function createGame(string $gameId, array $playerIds): void
    {
        //todo create party with list of player in redis

        //todo use neo4j
        //createGameWithParticipants($gameId, $playerIds);
        //for k in range(len(playerIds)):
            //createIdHistoire
          //  createStory($gameId , $storyId)
            //for j in range(len(playerIds)):
              //  createScript($storyId,$scriptId, j)

        //todo create the list of script in order 
    }

    public function setRoomType(string $roomType): void
    {
         $this->requetesRedis->createParty($roomType, "test"); 
    }
}
