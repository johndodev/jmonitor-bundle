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

    public function collect()
    {
        // LA
        $result = $this->connection->fetchAllAssociative("SELECT EVENT_NAME, COUNT_STAR FROM performance_schema.events_statements_summary_global_by_event_name WHERE EVENT_NAME IN ('statement/sql/select', 'statement/sql/insert', 'statement/sql/update', 'statement/sql/delete')");

        return $result;
    }
}
