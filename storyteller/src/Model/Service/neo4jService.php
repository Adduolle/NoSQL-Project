<?php
namespace App\Service;

use GraphAware\Neo4j\Client\ClientBuilder;
use GraphAware\Neo4j\Client\ClientInterface;

class Neo4jService
{
    private ClientInterface $client;

    public function __construct()
    {
        $this->client = ClientBuilder::create()
            ->withDriver('neo4j', 'neo4j://localhost:7474', 'neo4j', 'neo4j1234')
            ->build();
    }

    public function run(string $query, array $params = [])
    {
        return $this->client->run($query, $params);
    }
}
