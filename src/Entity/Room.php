<?php

namespace App\Entity;

class Room
{
    private int $id;
    private array $players;
    private string $code;

    public function __construct(int $id, string $code, array $players)
    {
        $this->id = $id;
        $this->code = $code;
        $this->players = $players;
    }

    public function getCode(): string { return $this->code; }
    public function setCode(string $code): self { $this->code = $code; return $this; }

    public function getPlayers():array{ return $this->players; }
    public function getId():int{ return $this->id; }
}
