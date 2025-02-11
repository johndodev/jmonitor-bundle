<?php

declare(strict_types=1);

namespace Johndodev\JmonitorBundle\Collector;

use Doctrine\DBAL\Connection;

class MysqlCollector implements CollectorInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function collect(): array
    {
        // return $this->connection->fetchAllKeyValue("SELECT EVENT_NAME, COUNT_STAR FROM performance_schema.events_statements_summary_global_by_event_name WHERE EVENT_NAME IN ('statement/sql/select', 'statement/sql/insert', 'statement/sql/update', 'statement/sql/delete')");

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
            AND schema_name NOT IN ('performance_schema', 'information_schema', 'mysql')
        GROUP BY
            schema_name";

        return $this->connection->fetchAllAssociative($sql);
    }
}
