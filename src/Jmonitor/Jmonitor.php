<?php

declare(strict_types=1);

namespace Johndodev\JmonitorBundle\Jmonitor;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class Jmonitor
{
    private Client $client;
    private iterable $collectors;

    public function __construct(Client $client, #[AutowireIterator('jmonitor.collector')] iterable $collectors)
    {
        $this->client = $client;
        $this->collectors = $collectors;
    }

    public function collect()
    {
        $results = [];
        foreach ($this->collectors as $name => $collector) {
            $results[$name] = $collector->collect();
        }

        return $results;
    }
}
