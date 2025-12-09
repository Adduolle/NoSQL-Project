<?php

use App\Model\Service;
use GraphAware\Neo4j\Client\ClientBuilder;
use GraphAware\Neo4j\Client\ClientInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController
{
    private Neo4jService $neo4j;

    public function __construct(Neo4jService $neo4j)
    {
        $this->neo4j = $neo4j;
    }


    #[Route('/user', name: 'app_user_create', methods: ['GET'])]
    public function createUser(Request $request): Response
    {
        $userId = $request->query->get('userId');
        $name = $request->query->get('name');

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

    #[Route('/game', name: 'create_game', methods: ['GET'])]
    public function createGame(Request $request): Response
    {
        $gameId = $request->query->get('gameId');
        $userIds = $request->query->all('userIds'); // userIds[]=A&userIds[]=B

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



    #[Route('/story', name: 'app_story_create', methods: ['POST'])]
    public function createStory(Request $request): Response
    {
        $gameId = $request->request->get('gameId');
        $storyId = $request->request->get('storyId');

        if (!$gameId || !$storyId) {
            return new Response("Missing gameId or storyId", 400);
        }

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

        return new Response("Story $storyId created in game $gameId");
    }


    #[Route('/script', name: 'app_script_create', methods: ['POST'])]
    public function createScript(Request $request): Response
    {
        $storyId = $request->request->get('storyId');
        $scriptId = $request->request->get('scriptId');
        $order = $request->request->get('order');

        if (!$storyId || !$scriptId || $order === null) {
            return new Response("Missing storyId, scriptId or order", 400);
        }

        $query = '
            MATCH (s:Story {id: $storyId})
            CREATE (sc:Script {
                id: $scriptId,
                order: $order,
                createdAt: datetime()
            })
            CREATE (sc)-[:PART_OF]->(s)
            RETURN sc
        ';

        $params = [
            'storyId' => $storyId,
            'scriptId' => $scriptId,
            'order' => intval($order),
        ];

        $result = $this->neo4j->run($query, $params);

        return new Response("Script $scriptId added to story $storyId");
    }
    #[Route('/script/send', name: 'app_script_send', methods: ['POST'])]
    public function sendScript(Request $request): Response
    {
        $scriptId = $request->request->get('scriptId');
        $userId = $request->request->get('userId');
        $text = $request->request->get('text');

        if (!$scriptId || !$userId || !$text) {
            return new Response("Missing scriptId, userId or text", 400);
        }

        $query = '
            MATCH (sc:Script {id: $scriptId})
            MATCH (u:User {id: $userId})
            SET sc.text = $text,
                sc.sentAt = datetime()
            CREATE (u)-[:WROTE]->(sc)
            RETURN sc
        ';

        $params = [
            'scriptId' => $scriptId,
            'userId' => $userId,
            'text' => $text,
        ];

        $this->neo4j->run($query, $params);

        return new Response("Script $scriptId sent by user $userId");
    }

}