<?php

declare(strict_types=1);

namespace Johndodev\JmonitorBundle\Collector;

use Doctrine\DBAL\Connection;

class MysqlSlowQueriesCollector implements CollectorInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function collect(): array
    {
        $sql = "SELECT
    DIGEST_TEXT AS query,
    SCHEMA_NAME AS db_name,
    COUNT_STAR AS execution_count,
    SUM_TIMER_WAIT / 1000000000 AS total_time_ms,
    AVG_TIMER_WAIT / 1000000000 AS avg_time_ms
FROM
    performance_schema.events_statements_summary_by_digest
WHERE SCHEMA_NAME IS NOT NULL AND SCHEMA_NAME NOT IN ('performance_schema', 'information_schema', 'mysql')
ORDER BY
    avg_time_ms DESC
LIMIT 10";

        return $this->connection->fetchAllAssociative($sql);
    }

    public function getVersion(): int
    {
        return 1;
    }
}
