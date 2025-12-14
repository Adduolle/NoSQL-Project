<?php

namespace App\Service;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;

class GameManager
{
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

    public function createTestGame(): Cookie
    {
        // Création du cookie de la game test
        $cookie = Cookie::create('game_0')
            ->withValue(json_encode([
                'code' => '2945',
                'name' => 'Partie Test',
                'round'=>0,
            ]))
            ->withExpires(new \DateTime('+1 day')) // cookie valide 1 jour
            ->withPath('/')
            ->withHttpOnly(false);
        return $cookie;
    }

    public function getGameById(string $gameId, Request $request): ?array
    {
        // On récupère la game existante
        $gameValue = $request->cookies->get($gameId);
        // On décode ses infos
        $game=[
            'code' => json_decode($gameValue, true)['code'],
            'name' => json_decode($gameValue, true)['name'],
            'round'=>json_decode($gameValue, true)['round'],
        ];
        return $game;
    }

    public function checkOrCreateGameTest(Request $request): Cookie
    {
        $cookieName = 'game_0';
        $cookieValue = $request->cookies->get($cookieName);

        if (!$cookieValue) {
            // Si le cookie n'existe pas, créer un nouveau Cookie
            return $this->createTestGame();
        }

        // Si le cookie existe, recréer un objet Cookie à partir de la valeur
        return Cookie::create($cookieName)
            ->withValue($cookieValue)
            ->withPath('/')
            ->withHttpOnly(false)
            ->withExpires(new \DateTime('+1 day'));
    }

    public function modifyGameRound(string $gameId, int $newRound, Request $request): Cookie
    {
        // On modifie le round de la game
        $gameValue = $request->cookies->get($gameId);
        $gameData = json_decode($gameValue, true);
        $gameData['round'] = $newRound;

        $cookie = Cookie::create($gameId)
            ->withValue(json_encode($gameData))
            ->withExpires(new \DateTime('+1 day')) // cookie valide 1 jour
            ->withPath('/')
            ->withHttpOnly(false);
        return $cookie;
    }

    public function getGameRound(string $gameId, Request $request): ?int
    {
        // On récupère le round de la game
        $gameValue = $request->cookies->get($gameId);
        $gameData = json_decode($gameValue, true);
        return $gameData['round'];
    }


}
