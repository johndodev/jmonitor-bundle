<?php

declare(strict_types=1);

namespace Johndodev\JmonitorBundle\Collector;

interface CollectorInterface
{
    public function collect();

    public function getVersion(): int;
}
