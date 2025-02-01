<?php

declare(strict_types=1);

namespace Johndodev\JmonitorBundle\Jmonitor;

class Jmonitor
{
    private Client $client;
    private iterable $collectors;

    public function __construct(Client $client, iterable $collectors)
    {
        $this->client = $client;
        $this->collectors = $collectors;
    }

    public function collectMetrics(): array
    {
        $results = [];

        foreach ($this->collectors as $name => $collector) {
            $results[$name] = $collector->collect();
        }

        return $results;
    }
}
