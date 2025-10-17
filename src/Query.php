<?php declare(strict_types=1);

/*
 * Copyright 2024 IBM
 * All Rights Reserved
 * Apache License 2.0 ... see LICENSE file
 * 
 * Original Author: Matthew Wiltzius <matthew.wiltzius@ibm.com>
 */

namespace Mapepire;

enum QueryState: int
{
    case NOT_YET_RUN         = 1;
    case RUN_MORE_DATA_AVAIL = 2;
    case RUN_DONE            = 3;
    case ERROR               = 4;
}

class Query
{
    public static array $globalQueryList = [];

    private QueryState $queryState;
    private SQLJob     $job;
    private string     $sql;
    private ?array     $parameters;
    private bool       $isClCommand;
    private            $correlationId;

    public function __construct(SQLJob $job, string $sql, ?array $options)
    {
        $this->job         = $job;
        $this->sql         = $sql;
        $this->parameters  = $options['parameters'] ?? [];
        $this->isClCommand = $options['isClCommand'] ?? false;
        $this->queryState  = QueryState::NOT_YET_RUN;

        self::$globalQueryList[] = $this;
    }

    public function __destruct()
    {
        $key = array_search($this, self::$globalQueryList, true);
        unset(self::$globalQueryList[$key]);
    }

    private function executeQuery(array $query): array
    {
        $this->job->send(json_encode($query));
        $results = json_decode($this->job->receive(), true);
        return $results;
    }

    public function prepareSqlExecute(): array
    {
        if ($this->queryState == QueryState::RUN_DONE) {
            throw new \RuntimeException("Statement has already been fully run");
        }
        $queryObject = [
            'id'         => $this->job->getNewUniqueId('prepare_sql_execute'),
            'type'       => 'prepare_sql_execute',
            'sql'        => $this->sql,
            'rows'       => 0,
            'parameters' => $this->parameters,
        ];
        $results = $this->executeQuery($queryObject);

        $success = (bool)($results['success'] ?? false);
        if (!$success && !$this->isClCommand) {
            $this->queryState = QueryState::ERROR;
            $errorKeys = ['error', 'sql_state', 'sql_rc'];
            $errorList = [];
            foreach ($errorKeys as $key) {
                if (array_key_exists($key, $results)) {
                    $errorList[$key] = $results[$key];
                }
            }
            if (count($errorList) == 0) {
                $errorList['error'] = "failed to run query for unknown reason";
            }
            throw new \RunetimeException(json_encode($errorList));
        }

        $isDone = (bool)($results['is_done'] ?? false);
        $this->queryState = ($isDone) ? QueryState::RUN_DONE : QueryState::RUN_MORE_DATA_AVAIL;

        $this->correlationId = $results['id'];

        return $results;
    }
}