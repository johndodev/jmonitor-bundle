<?php

declare(strict_types=1);

namespace Johndodev\JmonitorBundle\Collector\Redis;

use Johndodev\JmonitorBundle\Collector\CollectorInterface;
use Johndodev\JmonitorBundle\Exceptions\CollectorException;
use Predis\Response\Error;
use Relay\Relay;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class RedisCollector implements CollectorInterface
{
    private \Redis|\RedisArray|\RedisCluster|\Predis\ClientInterface|Relay $redis;

    public function __construct(\Redis|\RedisArray|\RedisCluster|\Predis\ClientInterface|Relay|string $redis)
    {
        if (is_string($redis) && class_exists(RedisAdapter::class)) {
            $redis = RedisAdapter::createConnection($redis);
        }

        $this->redis = $redis;
    }

    public function collect(): array
    {
        $infos = $this->redis->info();

        if (!$infos) {
            return [];
        }

        if ($infos instanceof Error) {
            throw new CollectorException($this, 'Redis connection error: ' . $infos->getMessage());
        }

        $infos = $this->flatten($infos);

        return [
            'server' => [
                'version' => $infos['redis_version'] ?? null,
                'mode' => $infos['redis_mode'] ?? null,
                'port' => $infos['tcp_port'] ?? null,
                'uptime' => $infos['uptime_in_seconds'] ?? null,
            ],
            'clients' => [
                'connected' => $infos['connected_clients'] ?? null,
            ],
            'memory' => [
                'used' => $infos['used_memory'] ?? null,
                'used_rss' => $infos['used_memory_rss'] ?? null,
                'used_peak' => $infos['used_memory_peak'] ?? null,
                'max_memory' => $infos['maxmemory'] ?? null,
                'max_memory_policy' => $infos['maxmemory_policy'] ?? null,
            ],
            'persistence' => [
                'rdb_bgsave_in_progress' => $infos['rdb_bgsave_in_progress'] ?? null,
                'rdb_last_save_time' => $infos['rdb_last_save_time'] ?? null,
                'rdb_changes_since_last_save' => $infos['rdb_changes_since_last_save'] ?? null,
                'rdb_last_bgsave_status' => $infos['rdb_last_bgsave_status'] ?? null,
                'rdb_last_bgsave_time' => $infos['rdb_last_bgsave_time'] ?? null,
                'aof_enabled' => $infos['aof_enabled'] ?? null,
                'aof_rewrite_in_progress' => $infos['aof_rewrite_in_progress'] ?? null,
                'aof_last_rewrite_time_sec' => $infos['aof_last_rewrite_time_sec'] ?? null,
                'aof_last_bgrewrite_status' => $infos['aof_last_bgrewrite_status'] ?? null,
                'aof_last_cow_size' => $infos['aof_last_cow_size'] ?? null,
                'aof_current_size' => $infos['aof_current_size'] ?? null,
                'aof_rewrite_base_size' => $infos['aof_rewrite_base_size'] ?? null,
            ],
            'stats' => [
                'total_connections_received' => $infos['total_connections_received'] ?? null,
                'total_commands_processed' => $infos['total_commands_processed'] ?? null,
                'instantaneous_ops_per_sec' => $infos['instantaneous_ops_per_sec'] ?? null,
                'rejected_connections' => $infos['rejected_connections'] ?? null,
                'expired_keys' => $infos['expired_keys'] ?? null,
                'evicted_keys' => $infos['evicted_keys'] ?? null,
                'evicted_clients' => $infos['evicted_clients'] ?? null,
                'keyspace_hits' => $infos['keyspace_hits'] ?? null,
                'keyspace_misses' => $infos['keyspace_misses'] ?? null,
                'tracking_total_keys' => $infos['tracking_total_keys'] ?? null,
                'total_error_replies' => $infos['total_error_replies'] ?? null,
                'total_reads_processed' => $infos['total_reads_processed'] ?? null,
                'total_writes_processed' => $infos['total_writes_processed'] ?? null,
                'acl_access_denied_auth' => $infos['acl_access_denied_auth'] ?? null,
            ],
            'replication' => [
                'role' => $infos['role'] ?? null,
                'connected_slaves' => $infos['connected_slaves'] ?? null,
            ],
            'cpu' => [
                'used_sys' => $infos['used_cpu_sys'] ?? null,
                'used_user' => $infos['used_cpu_user'] ?? null,
            ],
            'databases' => iterator_to_array($this->getDatabases($infos)),
        ];
    }

    public function getVersion(): int
    {
        return 1;
    }

    private function flatten(array $array): array
    {
        return array_reduce($array, function ($carry, $item) {
            if (is_array($item)) {
                foreach ($item as $key => $value) {
                    $carry[$key] = $value;
                }
            } else {
                $carry[$item] = $item;
            }

            return $carry;
        }, []);
    }

    private function getDatabases(array $infos): \Traversable
    {
        foreach ($infos as $k => $v) {
            if (str_starts_with($k, 'db')) {
                yield $k => $v;
            }
        }
    }
}
