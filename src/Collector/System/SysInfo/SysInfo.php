<?php

declare(strict_types=1);

namespace Johndodev\JmonitorBundle\Collector\System\SysInfo;

use Johndodev\JmonitorBundle\Collector\System\SysInfo\Adapter\AdapterInterface;

class SysInfo
{
    private AdapterInterface $adapter;

    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    public function getTotalMemory(): ?int
    {
        return $this->adapter->getTotalMemory();
    }

    public function getAvailableMemory(): ?int
    {
        return $this->adapter->getAvailableMemory();
    }

    public function getLoadPercent(): ?int
    {
        return $this->adapter->getLoadPercent();
    }

    public function getCoreCount(): int
    {
        return $this->adapter->getCoreCount();
    }

    public function getDiskTotalSpace(string $path): int
    {
        return $this->adapter->getDiskTotalSpace($path);
    }

    public function getDiskFreeSpace(string $path): int
    {
        return $this->adapter->getDiskFreeSpace($path);
    }

    public function clearPropertyCache(): void
    {
        $this->adapter->clearPropertyCache();
    }
}
