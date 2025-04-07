<?php

declare(strict_types=1);

namespace Johndodev\JmonitorBundle\Collector\System\SysInfo\Adapter;

use Johndodev\JmonitorBundle\Collector\System\SysInfo\Exceptions\SysInfoException;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class LinuxSysInfo extends AbstractSysInfo
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
        return $this->getCoreCount() ? (int) ((sys_getloadavg()[0] * 100) / $this->getCoreCount()) : null;
    }

    public function getCoreCount(): int
    {
        return $this->getPropertyCache('core_count', function () {
            return $this->getCacheValue('JMONITOR.CORE_COUNT', function (CacheItemInterface $item) {
                $item->expiresAfter(60 * 60 * 24 * 7);  // 7 days

                return (int) trim(shell_exec('nproc --all'));
            });
        });
    }

    public function getOsPrettyName(): ?string
    {
        return $this->getOsRelease('PRETTY_NAME') ?: (trim($this->getOsRelease('NAME').' '.$this->getOsRelease('VERSION')));
    }

    public function getUptime(): ?int
    {
        $uptime = file_get_contents('/proc/uptime');

        if ($uptime === false) {
            return null;
        }

        $uptime = explode(' ', $uptime);

        if (isset($uptime[0])) {
            return (int) $uptime[0];
        }

        return null;
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

    /**
     * Ex :
     * array:9 [
     * "PRETTY_NAME" => "Debian GNU/Linux 11 (bullseye)"
     * "NAME" => "Debian GNU/Linux"
     * "VERSION_ID" => "11"
     * "VERSION" => "11 (bullseye)"
     * "VERSION_CODENAME" => "bullseye"
     * "ID" => "debian"
     * "HOME_URL" => "https://www.debian.org/"
     * "SUPPORT_URL" => "https://www.debian.org/support"
     * "BUG_REPORT_URL" => "https://bugs.debian.org/"
     * ]
 */
    private function getOsRelease(string $key): ?string
    {
        $osRelease = $this->getPropertyCache('os_release', function () {
            return $this->getCacheValue('JMONITOR.OS_RELEASE', function (CacheItemInterface $item) {
                $item->expiresAfter(60 * 60 * 24 * 7);  // 7 days

                $output = file_get_contents('/etc/os-release');
                $lines = explode("\n", $output);
                $lines = array_filter($lines);

                $osRelease = [];
                foreach ($lines as $line) {
                    [$key, $value] = explode('=', $line);
                    $osRelease[$key] = trim($value, '"');
                }

                return $osRelease;
            });
        });

        return $osRelease[$key] ?? null;
    }
}
