<?php
namespace App\Service;

use Laudis\Neo4j\ClientBuilder;
use Laudis\Neo4j\Contracts\ClientInterface;

class Neo4JService
{
    private ClientInterface $client;

    public function __construct()
    {
        $this->client = ClientBuilder::create()
            ->withDriver('default', 'bolt://neo4j:password@neo4j:7687')
            ->build();
    }

    public function run(string $query, array $params = [])
    {
        return $this->client->run($query, $params);
    }
}
