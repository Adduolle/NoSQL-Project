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

    public function createGame(string $gameId, string $roomType): Response
    {
        if (!$gameId) {
            return new Response("Missing gameId or userIds[]", 400);
        }

        $query = '
            CREATE (g:Game {id: $gameId, createdAt: datetime()})
            RETURN g
        ';

        $params = [
            'gameId' => $gameId,
        ];

        $result = $this->neo4j->run($query, $params);

        return new Response("Game $gameId created");
    }

    public function createStories(string $gameId, array $players): Response
    {
        if (!$gameId || !$players) {
            return new Response("Missing gameId or players info", 400);
        }

        foreach ($players as $player) {
            $player = json_decode($player, true);
            $playerId = $player['id'];
            $storyId = $gameId . '_story_' . $playerId;

            // Crée la story du joueur
            $query = '
                MATCH (g:Game {id: $gameId})
                CREATE (s:Story {id: $storyId, createdAt: datetime()})
                CREATE (g)-[:CONTAINS_STORY]->(s)
                RETURN s
            ';
            $params = [
                'gameId' => $gameId,
                'storyId' => $storyId,
            ];
            $result = $this->neo4j->run($query, $params);

            // Crée le premier script pour ce joueur
            $scriptId=$storyId . '_script_' . $playerId;
            $this->createFirstScript($playerId,$storyId, $storyId . '_script_' . $playerId);
            $this->createOtherScripts($playerId,$storyId, $scriptId, $players);
        }
        return new Response("Stories and scripts created in game $gameId");
    }

    public function createFirstScript(string $playerId, string $storyId, string $scriptId): Response
    {
        if (!$storyId || !$scriptId === null) {
            return new Response("Missing storyId or scriptId", 400);
        }
        $query = '
            MATCH (s:Story {id: $storyId})
            MATCH (u:User {id: $playerId})
            CREATE (sc:Script {
                id: $scriptId,
                createdAt: datetime()
            })
            CREATE (sc)-[:FIRST_OF]->(s)
            CREATE (u)-[:ASSIGNED {content: "", createdAt: datetime()}]->(sc)
            RETURN sc';
        $params = [
            'storyId' => $storyId,
            'scriptId' => $scriptId,
            'playerId' => $playerId,
        ];
        $result = $this->neo4j->run($query, $params);
        return new Response("First script $scriptId created for story $storyId");
    }

    public function createOtherScripts(string $playerId, string $storyId, string $firstScriptId, array $players): Response
    {
        if (!$storyId || !$playerId || $players === null) {
            return new Response("Missing storyId or scriptId", 400);
        }

        $query = '
            MATCH (s:Story {id: $storyId})
            MATCH (u:User {id: $playerId})
            MATCH (prev:Script {id: $prevScriptId})
            CREATE (sc:Script {
                id: $scriptId,
                createdAt: datetime()
            })
            CREATE (prev)-[:NEXT]->(sc)
            CREATE (u)-[:ASSIGNED {content: "", createdAt: datetime()}]->(sc)
            RETURN sc
        ';
        $count = count($players);
        $posPlayer = array_search($playerId, array_column($players, 'id'));
        $decodedPlayers = array_map(fn($p) => json_decode($p, true), $players);
        $posPlayer = array_search($playerId, array_column($decodedPlayers, 'id'));
        $prevScriptId = $firstScriptId;
        for ($i=1; $i < $count; $i++) {
            if ($posPlayer + $i < $count) {
                $player = json_decode($players[$posPlayer + $i], true);
                $nextId = $player['id'];
            } else {
                $player = json_decode($players[($posPlayer + $i) % $count], true);
                $nextId = $player['id'];
            }
            $params = [
                'prevScriptId' => $prevScriptId,
                'storyId' => $storyId,
                'scriptId' => $storyId . '_script_' . $nextId,
                'playerId' => $nextId,
            ];
            $result = $this->neo4j->run($query, $params);
            $prevScriptId = $storyId . '_script_' . $nextId;
        }
        return new Response("Scrips added to story $storyId");
    }

    public function writeScript(string $scriptId, string $userId, string $text): Response
    {

        if (!$scriptId || !$userId || !$text) {
            return new Response("Missing scriptId, userId or text", 400);
        }

        $query = '
            MATCH (sc:Script {id: $scriptId})<-[a:ASSIGNED]-(u:User {id: $userId})
            SET a.content = $text,
                a.createdAt = datetime()
            RETURN a';

        $params = [
            'scriptId' => $scriptId,
            'userId' => $userId,
            'text' => $text,
        ];

        $this->neo4j->run($query, $params);

        return new Response("Script $scriptId sent by user $userId");
    }

    public function getAssignedTextForPlayerInRound(string $gameId, string $userId, int $round): ?string
    {
        if (!$gameId || !$userId || $round < 0) {
            return 'Aucun texte assigné';
        }

        $query = '
            MATCH (g:Game {id: $gameId})-[:CONTAINS_STORY]->(s:Story)<-[:FIRST_OF]-(firstScript:Script)
            MATCH path = (firstScript)-[:NEXT*' . $round . ']->(targetScript:Script)
            MATCH (u:User {id: $userId})-[a:ASSIGNED]->(targetScript)<-[:NEXT]-(textScript:Script)<-[assText:ASSIGNED]-(writer:User)
            RETURN assText.content AS content
        ';

        $params = [
            'gameId' => $gameId,
            'userId' => $userId,
        ];

        $result = $this->neo4j->run($query, $params);
        $record = $result->getRecord();

        return $record ? $record->get('content') : null;
    }

    public function getStories(string $gameId):object{
        $query = '
            MATCH (g:Game {id: $gameId})-[:CONTAINS_STORY]->(story:Story)<-[:FIRST_OF]-(first:Script)
            MATCH path = (first)-[:NEXT*0..]->(script:Script)
            MATCH (script)<-[a:ASSIGNED]-(u:User)
            WITH story, script, length(path) AS position, u, a
            ORDER BY story.id, position
            WITH story.id AS storyId, collect({
                scriptId: script.id,
                playerName: u.name,
                text: a.content
            }) AS scripts
            RETURN storyId, scripts
            ORDER BY storyId';

        $params = ['gameId' => $gameId];
        $result = $this->neo4j->run($query, $params);
        return $result;
    }

}