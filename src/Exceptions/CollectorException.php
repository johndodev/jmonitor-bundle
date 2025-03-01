<?php

declare(strict_types=1);

namespace Johndodev\JmonitorBundle\Exceptions;

use Johndodev\JmonitorBundle\Collector\CollectorInterface;

/**
 * When a collector fail or can't collect data
 */
class CollectorException extends \Exception
{
    private CollectorInterface $collector;

    public function __construct(CollectorInterface $collector, string $message)
    {
        parent::__construct(sprintf('Collector %s failed: %s', get_class($collector), $message));

        $this->collector = $collector;
    }

    public function getCollector(): CollectorInterface
    {
        return $this->collector;
    }
}
