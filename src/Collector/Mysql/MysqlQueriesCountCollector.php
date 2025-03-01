<?php

declare(strict_types=1);

namespace Johndodev\JmonitorBundle\Collector\Mysql;

use Doctrine\DBAL\Connection;
use Johndodev\JmonitorBundle\Collector\CollectorInterface;

class MysqlQueriesCountCollector implements CollectorInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function collect(): array
    {
        $sql = "SELECT
            schema_name,
            CAST(SUM(CASE WHEN digest_text LIKE 'SELECT%' THEN COUNT_STAR ELSE 0 END) AS UNSIGNED) AS total_select_queries,
            CAST(SUM(CASE WHEN digest_text LIKE 'INSERT%' THEN COUNT_STAR ELSE 0 END) AS UNSIGNED) AS total_insert_queries,
            CAST(SUM(CASE WHEN digest_text LIKE 'UPDATE%' THEN COUNT_STAR ELSE 0 END) AS UNSIGNED) AS total_update_queries,
            CAST(SUM(CASE WHEN digest_text LIKE 'DELETE%' THEN COUNT_STAR ELSE 0 END) AS UNSIGNED) AS total_delete_queries
        FROM
            performance_schema.events_statements_summary_by_digest
        WHERE
            schema_name IS NOT NULL
            AND schema_name NOT IN ('performance_schema', 'information_schema', 'mysql', 'phpmyadmin')
        GROUP BY
            schema_name";

        return $this->connection->fetchAllAssociative($sql);
    }

    public function getVersion(): int
    {
        return 1;
    }
}
