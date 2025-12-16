<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Neo4JService;

class RequetesNeo4j
{
    private Neo4JService $neo4j;
    public function __construct()
    {
        $this->neo4j = new Neo4JService();
    }

    public function createUser(string $userId, string $name): Response
    {

        if (!$userId || !$name) {
            return new Response("Missing userId or name", 400);
        }

        $query = '
            CREATE (u:User {id: $userId, name: $name})
            RETURN u
        ';

        $params = [
            'userId' => $userId,
            'name' => $name
        ];

        $result = $this->neo4j->run($query, $params);

        return new Response("User $name created");
    }

    public function createGame(string $gameId, string $roomType, array $players): Response
    {
        if (!$gameId || !$players) {
            return new Response("Missing gameId or userIds[]", 400);
        }

        $query = '
            WITH $gameId AS gameId, $userIds AS userIds
            CREATE (g:Game {id: gameId, createdAt: datetime()})
            WITH g, userIds
            UNWIND userIds AS uid
            MATCH (u:User {id: uid})
            CREATE (u)-[:PARTICIPATE_TO]->(g)
            RETURN g, collect(u) AS participants
        ';
        $userIds=[];
        foreach ($players as $player){
            $player = json_decode($player, true);
            $userIds[]=$player['id'];
        }

        $params = [
            'gameId' => $gameId,
            'userIds' => $userIds
        ];

        $result = $this->neo4j->run($query, $params);

        return new Response("Game $gameId created with players: " . implode(',', $userIds));
    }

    public function createStories(string $gameId, array $players): Response
    {
        if (!$gameId || !$players) {
            return new Response("Missing gameId or players info", 400);
        }

        // Décodage des joueurs
        $decodedPlayers = array_map(fn($p) => json_decode($p, true), $players);

        // Crée les stories et le premier script pour chaque joueur
        foreach ($decodedPlayers as $player) {
            $playerId = $player['id'];
            $storyId = $gameId . '_story_' . $playerId;

            // Crée la story du joueur
            $queryStory = '
                MATCH (g:Game {id: $gameId})
                CREATE (s:Story {id: $storyId, createdAt: datetime()})
                CREATE (g)-[:CONTAINS_STORY]->(s)
                RETURN s
            ';
            $this->neo4j->run($queryStory, [
                'gameId' => $gameId,
                'storyId' => $storyId,
            ]);

            // Premier script assigné au joueur propriétaire
            $firstScriptId = $storyId . '_script_' . $playerId;
            $this->createFirstScript($playerId, $storyId, $firstScriptId);

            // Crée les scripts suivants pour chaque autre joueur
            $this->createOtherScripts($storyId, $firstScriptId, $playerId, $decodedPlayers);
        }

        return new Response("Stories and scripts created in game $gameId");
    }

    public function createFirstScript(string $playerId, string $storyId, string $scriptId): void
    {
        $query = '
            MATCH (u:User {id: $playerId})
            MATCH (s:Story {id: $storyId})
            CREATE (sc:Script {id: $scriptId,content: "", createdAt: datetime()})
            CREATE (sc)-[:FIRST_OF]->(s)
            CREATE (u)-[:ASSIGNED { createdAt: datetime()}]->(sc)
            RETURN sc
        ';
        $this->neo4j->run($query, [
            'playerId' => $playerId,
            'storyId' => $storyId,
            'scriptId' => $scriptId,
        ]);
    }

    public function createOtherScripts(string $storyId, string $firstScriptId, string $ownerId, array $players): void
    {
        $prevScriptId = $firstScriptId;

        foreach ($players as $player) {
            $playerId = $player['id'];

            if ($playerId === $ownerId) continue; // on ne met pas le propriétaire dans sa propre story

            $scriptId = $storyId . '_script_' . $playerId;

            $query = '
                MATCH (prev:Script {id: $prevScriptId})
                MATCH (u:User {id: $playerId})
                CREATE (sc:Script {id: $scriptId,content: "" ,createdAt: datetime()})
                CREATE (prev)-[:NEXT]->(sc)
                CREATE (u)-[:ASSIGNED {createdAt: datetime()}]->(sc)
                RETURN sc
            ';
            $this->neo4j->run($query, [
                'prevScriptId' => $prevScriptId,
                'scriptId' => $scriptId,
                'storyId' => $storyId,
                'playerId' => $playerId,
            ]);

            $prevScriptId = $scriptId;
        }
    }


    public function writeScript(string $scriptId, string $text): Response
    {
        if (!$scriptId || !$text) {
            return new Response("Missing scriptId or text", 400);
        }

        $query = '
            MATCH (sc:Script {id: "$scriptId"})
            SET sc.content = "$text", sc.updatedAt = datetime()
            RETURN sc
        ';

        $this->neo4j->run($query, ['scriptId' => $scriptId, 'text' => $text]);

        return new Response("Script $scriptId updated");
    }


    public function getAssignedTextForPlayerInRound(string $gameId, string $userId, int $round): ?string
    {
        if (!$gameId || !$userId || $round < 0) {
            return 'Aucun texte assigné';
        }

        $query = '
            MATCH (g:Game {id: $gameId})-[:CONTAINS_STORY]->(s:Story)<-[:FIRST_OF]-(firstScript:Script)
            MATCH path = (firstScript)-[:NEXT*' . $round . ']->(targetScript:Script)
            MATCH (writer:User)-[:ASSIGNED]->(targetScript)
            WHERE writer.id <> $userId
            RETURN targetScript.content AS content
            LIMIT 1
        ';

        $params = [
            'gameId' => $gameId,
            'userId' => $userId,
        ];

        $result = $this->neo4j->run($query, $params);

        // Récupérer le premier record
        $record = $result->first(); // <-- Utiliser first() au lieu de getRecord()
        return $record ? $record->get('content') : null;
    }


    public function getScriptIdForPlayerInRound(string $gameId, string $userId, int $round): ?string
    {
        if (!$gameId || !$userId || $round < 0) {
            return null;
        }

        $query = '
            MATCH (g:Game {id: $gameId})-[:CONTAINS_STORY]->(s:Story)<-[:FIRST_OF]-(firstScript:Script)
            MATCH path = (firstScript)-[:NEXT*' . $round . ']->(targetScript:Script)
            MATCH (u:User {id: $userId})-[a:ASSIGNED]->(targetScript)
            RETURN targetScript.id AS scriptId
        ';

        $params = [
            'gameId' => $gameId,
            'userId' => $userId,
        ];

        $result = $this->neo4j->run($query, $params);

        if ($result->count() === 0) {
            return null;
        }

        $record = $result->first();

        return $record->get('scriptId');
    }


    public function getStories(string $gameId): array {
        $query = '
            MATCH (g:Game {id: $gameId})-[:CONTAINS_STORY]->(s:Story)
            MATCH (s)<-[:FIRST_OF]-(first:Script)
            MATCH path = (first)-[:NEXT*0..]->(sc:Script)
            MATCH (sc)-[a:ASSIGNED]->(u:User)
            WITH s, sc, u, a, length(path) AS position
            ORDER BY s, position
            WITH s,
                collect({name:u.name, text:a.content}) AS playersTexts
            RETURN collect({
                title: s.title,
                players: playersTexts
            }) AS stories
        ';
        $params = ['gameId' => $gameId];
        $result = $this->neo4j->run($query, $params);

        // Convertit le CypherList en array PHP
        $records = $result->first(); // récupère le premier enregistrement
        if ($records === null) return [];

        $stories = $records->get('stories');
        return $stories->toArray(); // <-- conversion en array PHP
    }


}