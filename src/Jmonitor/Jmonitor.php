<?php

declare(strict_types=1);

namespace Johndodev\JmonitorBundle\Jmonitor;

class Jmonitor
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }
}
