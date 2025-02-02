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
        $metrics = [];

        foreach ($this->collectors as $name => $collector) {
            $metrics[$name] = $collector->collect();
        }

        return $metrics;
    }
}
