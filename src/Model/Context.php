<?php

declare(strict_types=1);

namespace Johndodev\JmonitorBundle\Model;

class Context implements \JsonSerializable
{
    private int $nbDelayedExecution;
    private int $nbConsecutiveFailures;

    public function __construct(array $datas = [])
    {
        $this->nbDelayedExecution = $datas['nbDelayedExecution'] ?? 0;
        $this->nbConsecutiveFailures = $datas['nbConsecutiveFailures'] ?? 0;
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

    public function jsonSerialize(): mixed
    {
        return [
            'nbDelayedExecution' => $this->nbDelayedExecution,
            'nbConsecutiveFailures' => $this->nbConsecutiveFailures,
        ];
    }
}
