<?php

declare(strict_types=1);

namespace Johndodev\JmonitorBundle\Model;

class Context implements \JsonSerializable
{
    private int $nbDelayedExecution;
    private int $nbConsecutiveFailures;
    private array $metrics;

    public function __construct(array $datas = [])
    {
        $this->nbDelayedExecution = $datas['nbDelayedExecution'] ?? 0;
        $this->nbConsecutiveFailures = $datas['nbConsecutiveFailures'] ?? 0;
        $this->metrics = $datas['metrics'] ?? [];
    }

    public function getNbDelayedExecution(): int
    {
        return $this->nbDelayedExecution;
    }

    public function setNbDelayedExecution(int $nbDelayedExecution): void
    {
        $this->nbDelayedExecution = $nbDelayedExecution;
    }

    public function getNbConsecutiveFailures(): int
    {
        return $this->nbConsecutiveFailures;
    }

    public function setNbConsecutiveFailures(int $nbConsecutiveFailures): void
    {
        $this->nbConsecutiveFailures = $nbConsecutiveFailures;
    }

    public function getMetrics(): array
    {
        return $this->metrics;
    }

    public function setMetrics(array $metrics): void
    {
        $this->metrics = $metrics;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'nbDelayedExecution' => $this->nbDelayedExecution,
            'nbConsecutiveFailures' => $this->nbConsecutiveFailures,
            'metrics' => $this->metrics,
        ];
    }
}
