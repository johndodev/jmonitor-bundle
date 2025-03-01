<?php

declare(strict_types=1);

namespace Johndodev\JmonitorBundle\Collector\System;

use Johndodev\JmonitorBundle\Collector\CollectorInterface;
use Johndodev\JmonitorBundle\Collector\System\SysInfo\Adapter\AdapterInterface;
use Johndodev\JmonitorBundle\Collector\System\SysInfo\Adapter\LinuxAdapter;
use Johndodev\JmonitorBundle\Collector\System\SysInfo\SysInfo;

// TODO configurable /
class SystemCollector implements CollectorInterface
{
    private ?SysInfo $sysInfo = null;

    public function collect(): array
    {
        $sysInfo = $this->getSysInfos();

        $stats = [
            'disk' => [
                'total' => $sysInfo->getDiskTotalSpace('/'),
                'free' => $sysInfo->getDiskFreeSpace('/'),
            ],
            'cpu' => [
                'cores' => $sysInfo->getCoreCount(),
                'load' => $sysInfo->getLoad(),
            ],
            'ram' => [
                // $os->get
            ],
        ];

        dd($stats);
    }

    public function getVersion(): int
    {
        return 1;
    }

    private function getSysInfos(): SysInfo
    {
        return $this->sysInfo ??= new SysInfo($this->getAdapter());
    }

    private function getAdapter(): AdapterInterface
    {
        return new LinuxAdapter();
    }
}
