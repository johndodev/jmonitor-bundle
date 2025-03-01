<?php

namespace Johndodev\JmonitorBundle\Collector\System\SysInfo\Adapter;

interface AdapterInterface
{
    public function getTotalMemory(): int;

    public function getUsedMemory(): int;

    public function getLoad();

    public function getCoreCount(): int;

    public function getDiskTotalSpace(string $path): int;

    public function getDiskFreeSpace(string $path): int;

    public function reset(): void;
}
