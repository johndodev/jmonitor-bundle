<?php

declare(strict_types=1);

namespace Johndodev\JmonitorBundle\Collector\System;

use Johndodev\JmonitorBundle\Collector\CollectorInterface;
use Johndodev\JmonitorBundle\Collector\System\SysInfo\Adapter\AdapterInterface;
use Johndodev\JmonitorBundle\Collector\System\SysInfo\Adapter\LinuxAdapter;
use Johndodev\JmonitorBundle\Collector\System\SysInfo\Adapter\WindowsAdapter;
use Johndodev\JmonitorBundle\Collector\System\SysInfo\SysInfo;
use Psr\Cache\CacheItemPoolInterface;

// TODO configurable /
class SystemCollector implements CollectorInterface
{
    private ?SysInfo $sysInfo = null;

    private CacheItemPoolInterface $cache;

    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

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
                'load' => $sysInfo->getLoadPercent(),
            ],
            'ram' => [
                'total' => $sysInfo->getTotalMemory(),
                'available' => $sysInfo->getAvailableMemory(),
            ],
        ];

        $sysInfo->clearPropertyCache();

        return $stats;
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
        if (PHP_OS_FAMILY === 'Windows') {
            return new WindowsAdapter($this->cache);
        }

        return new LinuxAdapter($this->cache);
    }
}
