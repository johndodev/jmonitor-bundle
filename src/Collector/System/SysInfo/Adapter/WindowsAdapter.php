<?php

declare(strict_types=1);

namespace Johndodev\JmonitorBundle\Collector\System\SysInfo\Adapter;

use Johndodev\JmonitorBundle\Collector\System\SysInfo\Exceptions\SysInfoException;
use Psr\Cache\CacheItemPoolInterface;

class WindowsAdapter extends LinuxAdapter
{
    public function __construct(CacheItemPoolInterface $cache)
    {
        parent::__construct($cache);

        $this->assertFunctionAvailable('shell_exec');
        $this->assertFunctionAvailable('getenv');
    }

    public function getTotalMemory(): ?int
    {
        $output = shell_exec('powershell -Command "Get-WmiObject -Class Win32_OperatingSystem | Select-Object TotalVisibleMemorySize"');

        preg_match('/(\d+)/', $output ?: '', $matches);

        if (isset($matches[1])) {
            return (int) $matches[1] * 1024;
        }

        return null;
    }

    public function getAvailableMemory(): ?int
    {
        $output = shell_exec('powershell -Command "Get-WmiObject -Class Win32_OperatingSystem | Select-Object FreePhysicalMemory"');

        preg_match('/(\d+)/', $output ?: '', $matches);

        if (isset($matches[1])) {
            return (int) $matches[1] * 1024;
        }

        return null;
    }

    public function getCoreCount(): int
    {
        $nb = (int) getenv('NUMBER_OF_PROCESSORS');

        if ($nb > 0) {
            return $nb;
        }

        $output = shell_exec('powershell -Command "Get-WmiObject -Class Win32_Processor | Select-Object NumberOfCores"');

        preg_match_all('/\d+/', $output ?: '', $matches);

        $nb = $matches[0][0] ?? null;

        if ($nb === null) {
            throw new SysInfoException('Unable to determine number of cores of windows');
        }

        return (int) $nb;
    }

    public function getLoadPercent(): ?int
    {
        $output = shell_exec('powershell -Command "Get-WmiObject -Class Win32_Processor | Select-Object LoadPercentage"');

        if (!$output) {
            return null;
        }

        preg_match('/(\d+)/', $output, $matches);

        if (isset($matches[1])) {
            return (int) $matches[1];
        }

        return null;
    }
}
