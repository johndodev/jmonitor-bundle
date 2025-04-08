<?php

declare(strict_types=1);

namespace Johndodev\JmonitorBundle\Collector\Mysql;

use Doctrine\DBAL\Connection;
use Johndodev\JmonitorBundle\Collector\CollectorInterface;

class MysqlVariablesCollector implements CollectorInterface
{
    private Connection $connection;

    private const VARIABLES = [
        'innodb_buffer_pool_size',
        'innodb_buffer_pool_read_requests',
        'innodb_buffer_pool_reads', // (indique un « cache miss » lorsque le moteur lit depuis le disque).
        'max_connections',
        'version',
        'version_comment',
        'long_query_time',
        'tmp_table_size',          // a checker
        'max_heap_table_size',      // a checker
        'sort_buffer_size',     // a checker
        'join_buffer_size',     // a checker
        'thread_cache_size',        // a checker
        'table_open_cache',
    ];

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function collect(): array
    {
        $result = $this->connection->fetchAllAssociative("SHOW VARIABLES WHERE Variable_name IN ('" . implode("', '", self::VARIABLES) . "')");

        return array_column($result, 'Value', 'Variable_name');
    }

    public function getVersion(): int
    {
        return 1;
    }
}
