<?php
require 'vendor/autoload.php';
use GraphAware\Neo4j\Client\ClientBuilder;

$createPlayer = '
    CREATE (u:User {id: $userId, name: $nameId})
    RETURN u;
';
$createGameWithPlays = '
    WITH "game1" AS gameId,
    ["000002", "000003", "000004"] AS userIds

    // 1) Créer la partie
    CREATE (g:Game {id: gameId, createdAt: datetime()})

    // 2) Récupérer les utilisateurs
    WITH g, userIds
    UNWIND userIds AS uid
    MATCH (u:User {id: uid})
    CREATE (u)-[p:PARTICIPATE_TO]->(g)

    RETURN g,collect(u) AS participants
';

//créer une story dans game

$createStory = '
    MATCH (g:Game {id: $gameId})
    CREATE (s:Story {id: $storyId, createdAt: datetime()})
    CREATE (g)-[:CONTAINS_STORY]->(s)
    RETURN s
    ';
$createScript = '
    MATCH (s:Story {id: $storyId})
    CREATE (sc:Script {id: $scriptId, order: $orderInStory, createdAt: datetime()})
    CREATE (s)<-[:PART_OF]-(sc)
    RETURN sc
    ';

$sendScript = '
    MATCH (sc:Script {id: $scriptId})
    MATCH (u:User {id: $userId})
    SET sc.text = $text, sc.sentAt = datetime()
    CREATE (u)-[:WROTE]->(sc)
    RETURN sc
';



$client = ClientBuilder::create()
    ->withDriver('neo4j', 'neo4j://localhost:7474', 'neo4j', 'neo4j1234')
    ->build();


// partie à mettre 
$params = [// changer les paramères avec les valeurs crées
    'userId' => "000002",
    'nameId' => "Alice"
];

$result = $client->run($query, $params);

print_r($result->first()->get('u'));
