<?php

declare(strict_types=1);

namespace Johndodev\JmonitorBundle\Jmonitor;

use Johndodev\JmonitorBundle\Collector\CollectorInterface;

class Jmonitor
{
    /**
     * @var CollectorInterface[]
     */
    private iterable $collectors;

    public function __construct(iterable $collectors)
    {
        $this->collectors = $collectors;
    }

    public function collectMetrics(): array
    {
        $metrics = [];

        foreach ($this->collectors as $name => $collector) {
            $metrics[] = [
                'metrics' => $collector->collect(),
                'version' => $collector->getVersion(),
                'name' => $name,
            ];
        }

        return $metrics;
    }
}
