<?php

declare(strict_types=1);

namespace Johndodev\JmonitorBundle\Collector\Mysql;

use Doctrine\DBAL\Connection;
use Johndodev\JmonitorBundle\Collector\CollectorInterface;

class MysqlStatusCollector implements CollectorInterface
{
    private Connection $connection;

    private const GLOBAL_VARIABLES = [
        'Uptime',
        'Threads_connected',
        'Threads_running',
        'Questions',
        'Aborted_connects',
        'Aborted_clients',
        'Com_select',
        'Com_insert',
        'Com_update',
        'Com_delete',
        'Max_used_connections',
        'wait_timeout',
    ];

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function collect(): array
    {
        $result = $this->connection->fetchAllAssociative("SHOW GLOBAL STATUS WHERE Variable_name IN ('" . implode("', '", self::GLOBAL_VARIABLES) . "')");

        return array_column($result, 'Value', 'Variable_name');
    }

    public function getVersion(): int
    {
        return 1;
    }
}
