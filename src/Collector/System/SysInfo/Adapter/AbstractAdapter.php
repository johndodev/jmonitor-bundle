<?php

declare(strict_types=1);

namespace Johndodev\JmonitorBundle\Collector\System\SysInfo\Adapter;

use Johndodev\JmonitorBundle\Collector\System\SysInfo\Exceptions\SysInfoException;

abstract class AbstractAdapter implements AdapterInterface
{
    private array $availableFunctions = [];

    public function getDiskTotalSpace(string $path): int
    {
        return (int) disk_total_space($path);
    }

    public function getDiskFreeSpace(string $path): int
    {
        return (int) disk_free_space($path);
    }

    public function assertFunctionAvailable(string $name): void
    {
        if (!isset($this->availableFunctions[$name])) {
            $this->assertFunctionExist($name);
            $this->assertFunctionIsAvailable($name);
        }

        $this->availableFunctions[$name] = true;
    }

    private function assertFunctionExist(string $name): void
    {
        if (!function_exists($name)) {
            throw new SysInfoException(sprintf('Function %s does not exist, please open an issue in github.', $name));
        }
    }

    private function assertFunctionIsAvailable(string $name): void
    {
        static $disabledFunctions = null;
        $disabledFunctions ??= explode(',', ini_get('disable_functions'));

        if (in_array($name, $disabledFunctions)) {
            throw new SysInfoException(sprintf('Function %s is disabled in disable_functions in php.ini.', $name));
        }
    }

    public function reset(): void
    {
    }
}
