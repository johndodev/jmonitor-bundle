<?php

declare(strict_types=1);

namespace Johndodev\JmonitorBundle\Collector\System;

use Johndodev\JmonitorBundle\Collector\CollectorInterface;
use Johndodev\JmonitorBundle\Collector\System\SysInfo\Adapter\SysInfoInterface;
use Johndodev\JmonitorBundle\Collector\System\SysInfo\Adapter\LinuxSysInfo;
use Johndodev\JmonitorBundle\Collector\System\SysInfo\Adapter\WindowsSysInfo;
use Psr\Cache\CacheItemPoolInterface;

// TODO configurable /
class SystemCollector implements CollectorInterface
{
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
            'os' => [
                'pretty_name' => $sysInfo->getOsPrettyName(),
                'uptime' => $sysInfo->getUptime(),
            ],
            'time' => time(),
            'hostname' => gethostname(),
        ];

        $sysInfo->clearPropertyCache();

        return $stats;
    }

    public function getVersion(): int
    {
        return 1;
    }

    private function getSysInfos(): SysInfoInterface
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return new WindowsSysInfo($this->cache);
        }

        return new LinuxSysInfo($this->cache);
    }
}
