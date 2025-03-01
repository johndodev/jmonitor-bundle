<?php

namespace Johndodev\JmonitorBundle\Collector\System\SysInfo\Adapter;

interface AdapterInterface
{
    public function getTotalMemory(): ?int;

    public function getAvailableMemory(): ?int;

    public function getLoadPercent(): ?int;

    public function getCoreCount(): int;

    public function getDiskTotalSpace(string $path): int;

    public function getDiskFreeSpace(string $path): int;

    public function clearPropertyCache(): void;
}
