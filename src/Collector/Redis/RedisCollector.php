<?php

declare(strict_types=1);

namespace Johndodev\JmonitorBundle\Collector\Redis;

use Johndodev\JmonitorBundle\Collector\CollectorInterface;
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

        return [
            'server' => [
                'version' => $infos['Server']['redis_version'] ?? null,
                'mode' => $infos['Server']['redis_mode'] ?? null,
                'port' => $infos['Server']['tcp_port'] ?? null,
                'uptime' => $infos['Server']['uptime_in_seconds'] ?? null,
            ],
            'clients' => [
                'connected' => $infos['Clients']['connected_clients'] ?? null,
            ],
            'memory' => [
                'used' => $infos['Memory']['used_memory'] ?? null,
                'used_rss' => $infos['Memory']['used_memory_rss'] ?? null,
                'used_peak' => $infos['Memory']['used_memory_peak'] ?? null,
                'max_memory' => $infos['Memory']['maxmemory'] ?? null,
                'max_memory_policy' => $infos['Memory']['maxmemory_policy'] ?? null,
            ],
            'persistence' => [
                'rdb_bgsave_in_progress' => $infos['Persistence']['rdb_bgsave_in_progress'] ?? null,
                'rdb_last_save_time' => $infos['Persistence']['rdb_last_save_time'] ?? null,
                'rdb_changes_since_last_save' => $infos['Persistence']['rdb_changes_since_last_save'] ?? null,
                'rdb_last_bgsave_status' => $infos['Persistence']['rdb_last_bgsave_status'] ?? null,
                'rdb_last_bgsave_time' => $infos['Persistence']['rdb_last_bgsave_time'] ?? null,
                'aof_enabled' => $infos['Persistence']['aof_enabled'] ?? null,
                'aof_rewrite_in_progress' => $infos['Persistence']['aof_rewrite_in_progress'] ?? null,
                'aof_last_rewrite_time_sec' => $infos['Persistence']['aof_last_rewrite_time_sec'] ?? null,
                'aof_last_bgrewrite_status' => $infos['Persistence']['aof_last_bgrewrite_status'] ?? null,
                'aof_last_cow_size' => $infos['Persistence']['aof_last_cow_size'] ?? null,
                'aof_current_size' => $infos['Persistence']['aof_current_size'] ?? null,
                'aof_rewrite_base_size' => $infos['Persistence']['aof_rewrite_base_size'] ?? null,
            ],
            'stats' => [
                'total_connections_received' => $infos['Stats']['total_connections_received'] ?? null,
                'total_commands_processed' => $infos['Stats']['total_commands_processed'] ?? null,
                'instantaneous_ops_per_sec' => $infos['Stats']['instantaneous_ops_per_sec'] ?? null,
                'rejected_connections' => $infos['Stats']['rejected_connections'] ?? null,
                'expired_keys' => $infos['Stats']['expired_keys'] ?? null,
                'evicted_keys' => $infos['Stats']['evicted_keys'] ?? null,
                'evicted_clients' => $infos['Stats']['evicted_clients'] ?? null,
                'keyspace_hits' => $infos['Stats']['keyspace_hits'] ?? null,
                'keyspace_misses' => $infos['Stats']['keyspace_misses'] ?? null,
                'tracking_total_keys' => $infos['Stats']['tracking_total_keys'] ?? null,
                'total_error_replies' => $infos['Stats']['total_error_replies'] ?? null,
                'total_reads_processed' => $infos['Stats']['total_reads_processed'] ?? null,
                'total_writes_processed' => $infos['Stats']['total_writes_processed'] ?? null,
                'acl_access_denied_auth' => $infos['Stats']['acl_access_denied_auth'] ?? null,
            ],
            'replication' => [
                'role' => $infos['Replication']['role'] ?? null,
                'connected_slaves' => $infos['Replication']['connected_slaves'] ?? null,
            ],
            'cpu' => [
                'used_sys' => $infos['CPU']['used_cpu_sys'] ?? null,
                'used_user' => $infos['CPU']['used_cpu_user'] ?? null,
            ],
            'Keyspace' => $infos['Keyspace'] ?? [],
        ];
    }

    public function getVersion(): int
    {
        return 1;
    }

}
