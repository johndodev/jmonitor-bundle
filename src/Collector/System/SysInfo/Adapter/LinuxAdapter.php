<?php

declare(strict_types=1);

namespace Johndodev\JmonitorBundle\Collector\System\SysInfo\Adapter;

use Johndodev\JmonitorBundle\Collector\System\SysInfo\Exceptions\SysInfoException;

class LinuxAdapter extends AbstractAdapter
{
    private array $memInfos = [];

    public function getTotalMemory(): int
    {
        return $this->getMemInfo('MemTotal');
    }

    public function getUsedMemory(): int
    {
        // TODO: Implement getUsedMemory() method.
    }

    public function getLoad()
    {
        return sys_getloadavg();
    }

    public function getCoreCount(): int
    {
        $this->assertFunctionAvailable('shell_exec');

        return (int) trim(shell_exec('nproc --all'));
    }

    private function getMemInfo(string $name): int
    {
        if (!$this->memInfos) {
            $this->loadMemInfos();
        }

        if (!isset($this->memInfos[$name])) {
            throw new SysInfoException(sprintf('MemInfo %s not found', $name));
        }
dd($this->memInfos);
        return $this->memInfos[$name];
    }

    private function loadMemInfos(): void
    {
        $this->assertFunctionAvailable('shell_exec');

        // $output = shell_exec('cat /proc/meminfo');
        $output = $this->fakeMemInfos();
        $lines = explode("\n", $output);

        $memInfos = [];
        foreach ($lines as $line) {
            if (empty($line)) {
                continue;
            }

            [$key, $value] = explode(':', $line);
            $memInfos[$key] = (int) preg_replace('/\D/', '', $value);
        }

        $this->memInfos = $memInfos;
    }

    public function reset(): void
    {
        $this->memInfos = [];
    }

    private function fakeMemInfos()
    {
        return <<<FAKE
MemTotal:       32864448 kB
MemFree:        15128188 kB
MemAvailable:   23036528 kB
Buffers:          130856 kB
Cached:          7186040 kB
SwapCached:            0 kB
Active:         11100360 kB
Inactive:        4684568 kB
Active(anon):    8922176 kB
Inactive(anon):        0 kB
Active(file):    2178184 kB
Inactive(file):  4684568 kB
Unevictable:       36996 kB
Mlocked:           27620 kB
SwapTotal:             0 kB
SwapFree:              0 kB
Zswap:                 0 kB
Zswapped:              0 kB
Dirty:               468 kB
Writeback:            12 kB
AnonPages:       8505052 kB
Mapped:          2429148 kB
Shmem:            435700 kB
KReclaimable:    1508484 kB
Slab:            1761092 kB
SReclaimable:    1508484 kB
SUnreclaim:       252608 kB
KernelStack:       13120 kB
PageTables:        49140 kB
SecPageTables:         0 kB
NFS_Unstable:          0 kB
Bounce:                0 kB
WritebackTmp:          0 kB
CommitLimit:    16432224 kB
Committed_AS:   12805744 kB
VmallocTotal:   34359738367 kB
VmallocUsed:       23592 kB
VmallocChunk:          0 kB
Percpu:            16768 kB
HardwareCorrupted:     0 kB
AnonHugePages:         0 kB
ShmemHugePages:        0 kB
ShmemPmdMapped:        0 kB
FileHugePages:         0 kB
FilePmdMapped:         0 kB
Unaccepted:            0 kB
HugePages_Total:       0
HugePages_Free:        0
HugePages_Rsvd:        0
HugePages_Surp:        0
Hugepagesize:       2048 kB
Hugetlb:               0 kB
DirectMap4k:      143360 kB
DirectMap2M:     6148096 kB
DirectMap1G:    28311552 kB
FAKE;

    }
}
