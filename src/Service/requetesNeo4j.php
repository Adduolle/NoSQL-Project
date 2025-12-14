<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class requetesNeo4j
{
    private Neo4JService $neo4j;

    public function __construct(Neo4JService $neo4j)
    {
        $this->neo4j = $neo4j;
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

    public function createGame(string $gameId, array $userIds): Response
    {
        if (!$gameId || !$userIds) {
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
        
        foreach ($players as $player) {
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
            CREATE (sc:Script {
                id: $scriptId,
                createdAt: datetime(),
            })
            CREATE (sc)-[:FIRST_OF]->(s)
            CREATE (u:User {id: $playerId})-[:ASSIGNED {content: "", createdAt: datetime()}]->(sc)
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
            MATCH (prev:Script {id: $prevScriptId})
            CREATE (sc:Script {
                id: $scriptId,
                createdAt: datetime(),
            })
            CREATE (prev)-[:NEXT]->(sc)
            CREATE (u:User {id: $playerId})-[:ASSIGNED {content: "", createdAt: datetime()}]->(sc)
            RETURN sc
        ';
        $count = count($players);
        $posPlayer = array_search($playerId, array_column($players, 'id'));
        $prevScriptId = $firstScriptId;
        for ($i=1; $i < $count; $i++) {
            if ($posPlayer + $i < $count) {
                $nextId = $players[$posPlayer + $i]['id'];
            } else {
                $nextId = $players[($posPlayer + $i) % $count]['id'];
            }
            $params = [
                'prevScriptId' => $prevScriptId,
                'storyId' => $storyId,
                'scriptId' => $storyId . '_script_' . $nextId,
                'playerId' => $playerId,
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
            MATCH (sc:Script {id: $scriptId})
            MATCH (u:User {id: $userId})
            CREATE (u)-[a:ASSIGNED {content: $text,createdAt: datetime()}]->(sc)
            RETURN w
        ';

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

}