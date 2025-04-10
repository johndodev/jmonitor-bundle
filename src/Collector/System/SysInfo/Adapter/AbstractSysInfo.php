<?php

declare(strict_types=1);

namespace Johndodev\JmonitorBundle\Collector\System\SysInfo\Adapter;

use Johndodev\JmonitorBundle\Collector\System\SysInfo\Exceptions\SysInfoException;
use Psr\Cache\CacheItemPoolInterface;

abstract class AbstractSysInfo implements SysInfoInterface
{
    private array $availableFunctions = [];

    /**
     * Used for values that basically never change, like core count, total memory, etc.
     */
    protected CacheItemPoolInterface $cache;

    /**
     * Used to store values that are not supposed to change during one collector run.
     * Cleared at the end of the collect
     */
    private array $propertyCache = [];

    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

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

    public function clearPropertyCache(): void
    {
        $this->propertyCache = [];
    }

    protected function getCacheValue(string $name, callable $callback): mixed
    {
        $item = $this->cache->getItem($name);

        if ($item->isHit()) {
            return $item->get();
        }

        $value = $callback($item);
        $item->set($value);

        $this->cache->save($item);

        return $value;
    }

    protected function getPropertyCache(string $name, callable $callback): mixed
    {
        if (array_key_exists($name, $this->propertyCache)) {
            return $this->propertyCache[$name];
        }

        return $this->propertyCache[$name] = $callback();
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
}
