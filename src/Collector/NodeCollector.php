<?php

declare(strict_types=1);

namespace Johndodev\JmonitorBundle\Collector;

use Doctrine\DBAL\Connection;

class NodeCollector implements CollectorInterface
{
    public function collect(): array
    {
        return [];
//        try {
//            $infos = sys_getloadavg();
//        } catch (\Error $exception) {
//            dd(get_class($exception));
//        }
//        dd($infos);
    }

    public function getVersion(): int
    {
        return 1;
    }
}
