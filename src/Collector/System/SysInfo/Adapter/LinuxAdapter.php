<?php

declare(strict_types=1);

namespace Johndodev\JmonitorBundle\Collector\System\SysInfo\Adapter;

use Johndodev\JmonitorBundle\Collector\System\SysInfo\Exceptions\SysInfoException;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class LinuxAdapter extends AbstractAdapter
{
    public function __construct(CacheItemPoolInterface $cache)
    {
        parent::__construct($cache);

        $this->assertFunctionAvailable('shell_exec');
    }

    public function getTotalMemory(): ?int
    {
        return $this->getMemInfo('MemTotal') * 1024;
    }

    public function getAvailableMemory(): ?int
    {
        return $this->getMemInfo('MemAvailable') * 1024;
    }

    public function getLoadPercent(): ?int
    {
        return sys_getloadavg()[0] * 100;
    }

    public function getCoreCount(): int
    {
        return $this->getCacheValue('CORE_COUNT', function (CacheItemInterface $item) {
            $item->expiresAfter(60 * 60 * 24 * 7);  // 7 days

            return (int) trim(shell_exec('nproc --all'));
        });
    }

    private function getMemInfo(string $name): int
    {
        $memInfo = $this->getPropertyCache('meminfos', function () {
            $output = shell_exec('cat /proc/meminfo');
            $lines = explode("\n", $output);
            $lines = array_filter($lines);

            $memInfos = [];
            foreach ($lines as $line) {
                [$key, $value] = explode(':', $line);
                $memInfos[$key] = (int) preg_replace('/\D/', '', $value);
            }

            return $memInfos;
        });

        if (!isset($memInfo[$name])) {
            throw new SysInfoException(sprintf('MemInfo %s not found', $name));
        }

        return $memInfo[$name];
    }
}
