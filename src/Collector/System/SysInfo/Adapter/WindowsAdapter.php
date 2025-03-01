<?php

declare(strict_types=1);

namespace Johndodev\JmonitorBundle\Collector\System\SysInfo\Adapter;

use Johndodev\JmonitorBundle\Collector\System\SysInfo\Exceptions\SysInfoException;

class WindowsAdapter extends LinuxAdapter
{
    public function getCoreCount(): int
    {
        $this->assertFunctionAvailable('getenv');

        $nb = (int) getenv('NUMBER_OF_PROCESSORS');

        if ($nb > 0) {
            return $nb;
        }

        $this->assertFunctionAvailable('shell_exec');

        $output = shell_exec('wmic CPU Get NumberOfCores');
        preg_match_all('/\d+/', $output, $matches);

        $nb = $matches[0][0] ?? null;

        if ($nb === null) {
            throw new SysInfoException('Unable to determine number of cores of windows');
        }

        return (int) $nb;
    }
}
