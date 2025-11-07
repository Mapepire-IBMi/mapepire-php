<?php declare(strict_types=1);

/*
 * Copyright 2024 IBM
 * All Rights Reserved
 * Apache License 2.0 ... see LICENSE file
 * 
 * Original Author: Matthew Wiltzius <matthew.wiltzius@ibm.com>
 */

namespace Mapepire;

/**
 * The QueryState tracks the execution state of a query
 */
enum QueryState: int
{
    case NOT_YET_RUN         = 1;
    case RUN_MORE_DATA_AVAIL = 2;
    case RUN_DONE            = 3;
    case ERROR               = 4;
}

/**
 * The Query class handles SQL query execution and result management
 */
class Query
{
    /** @var list<self> */
    public static array $globalQueryList = [];

    private QueryState $state = QueryState::NOT_YET_RUN;
    private SQLJob     $job;
    private string     $sql;
    private array      $parameters     = [];
    private bool       $isPrepared     = false;
    private bool       $isClCommand    = false;
    private bool       $isTerseResults = false;
    private int        $rows           = 100;
    private ?string    $correlationId  = null;

    /**
     * Set up Query object
     * @param SQLJob $job - A reference to the SQLJob managing the connection
     * @param string $sql - The SQL query string to execute
     * @param ?QueryOptions $options - A QueryOptions object controlling execution behavior
     */
    public function __construct(SQLJob $job, string $sql, ?QueryOptions $options)
    {
        $this->job            = $job;
        $this->sql            = $sql;

        $options = $options ?? [];
        $this->parameters     = $options->parameters ?? [];
        $this->isPrepared     = !empty($options->parameters);
        $this->isClCommand    = $options->isClCommand ?? false;
        $this->isTerseResults = $options->isTerseResults ?? false;
        
        self::$globalQueryList[] = $this;
    }

    /**
     * Destroy Query Object
     */
    public function __destruct()
    {
        // $key = array_search($this, self::$globalQueryList, true);
        // unset(self::$globalQueryList[$key]);
        try {$this->close();} catch (\Throwable $e) {}
    }

    private function executeQuery(array $query): array
    {
        $this->job->send(json_encode($query));
        $results = json_decode($this->job->receive(), true);
        return $results;
    }

    /**
     * Prepares and executes a SQL statement with parameters
     * @return array - server reply
     */
    public function prepareSqlExecute(): array
    {
        if ($this->state === QueryState::RUN_DONE)
            throw new \RuntimeException("Statement has already been fully run");

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
            $this->state = QueryState::ERROR;
            $errorKeys = ['error', 'sql_state', 'sql_rc'];
            $errorList = [];
            foreach ($errorKeys as $key) {
                if (array_key_exists($key, $results))
                    $errorList[$key] = $results[$key];
            }
            if (count($errorList) == 0)
                $errorList['error'] = "failed to run query for unknown reason";
            throw new \RuntimeException(json_encode($errorList));
        }

        $isDone = (bool)($results['is_done'] ?? false);
        $this->state = ($isDone) ? QueryState::RUN_DONE : QueryState::RUN_MORE_DATA_AVAIL;

        $this->correlationId = $results['id'];

        return $results;
    }

    /** Executes the query with optional row count limit. 
     * Handles different query types (SQL vs. CL command)
     * Updates query state based on response
     * Captures correlation ID for future fetch operations
     * @param ?int $rows - maximum number of rows to return (if SQL)
     * @return array - server reply
     */
    public function run(?int $rows = null): array
    {
        if ($rows == null)
            $rows = $this->rows;
        else
            $this->rows = $rows;

        // Check Query state first
        if ($this->state === QueryState::RUN_MORE_DATA_AVAIL)
            throw new \RuntimeException("Statement has already been run");
        elseif ($this->state === QueryState::RUN_DONE) 
            throw new \RuntimeException("Statement has already been fully run");

        $queryObject = [];
        if ($this->isClCommand)
            $queryObject = [
                'id'         => $this->job->getNewUniqueId('clcommand'),
                'type'       => 'cl',
                'terse'      => $this->isTerseResults,
                'cmd'        => $this->sql,
            ];
        else
            $queryObject = [
                'id'         => $this->job->getNewUniqueId('query'),
                'type'       => $this->isPrepared ? 'prepare_sql_execute' : 'sql',
                'sql'        => $this->sql,
                'terse'      => $this->isTerseResults,
                'rows'       => $rows,
                'parameters' => $this->parameters,
            ];

        $results = $this->executeQuery($queryObject);

        $success = (bool)($results['success'] ?? false);
        if (!$success && !$this->isClCommand) {
            $this->state = QueryState::ERROR;
            $errorKeys = ['error', 'sql_state', 'sql_rc'];
            $errorList = [];
            foreach ($errorKeys as $key) {
                if (array_key_exists($key, $results))
                    $errorList[$key] = $results[$key];
            }
            if (count($errorList) == 0)
                $errorList['error'] = "failed to run query for unknown reason";
            throw new \RuntimeException(json_encode($errorList));
        }

        $isDone = (bool)($results['is_done'] ?? false);
        $this->state = ($isDone) ? QueryState::RUN_DONE : QueryState::RUN_MORE_DATA_AVAIL;
        
        $this->correlationId = $results['id'];

        return $results;
    }

    /**
     * Retrieves additional rows when more results are available
     * @param int $rows maximum number of rows to retrieve
     * @return array - server reply
     */
    public function fetchMore(int $rows): array
    {
        if ($rows == null)
            $rows = $this->rows;
        else
            $this->rows = $rows;

        // Check Query state first
        if ($this->state === QueryState::NOT_YET_RUN)
            throw new \RuntimeException("Statement has not been run");
        elseif ($this->state === QueryState::RUN_DONE) 
            throw new \RuntimeException("Statement has already been fully run");

        $queryObject = [
            'id'         => $this->job->getNewUniqueId('fetchMore'),
            'cont_id'    => $this->correlationId,
            'type'       => 'sqlmore',
            'sql'        => $this->sql,
            'rows'       => $rows,
        ];

        $this->rows = $rows;
        $results = $this->executeQuery($queryObject);

        $success = (bool)($results['success'] ?? false);
        if (!$success && !$this->isClCommand) {
            $this->state = QueryState::ERROR;
            throw new \RuntimeException(($results['error'] ?? "Failed to run Query (unknown error)"));
        }

        $isDone = (bool)($results['is_done'] ?? false);
        $this->state = ($isDone) ? QueryState::RUN_DONE : QueryState::RUN_MORE_DATA_AVAIL;
        
        return $results;
    }

    /**
     * Send a close request to the server
     * @return array - server reply (if any)
     */
    public function close(): array
    {
        if (!$this->job->getSocket())
            throw new \RuntimeException("SQL Job not connected");
        if ($this->correlationId && ($this->state != QueryState::RUN_DONE)) {
            $this->state = QueryState::RUN_DONE;
            $queryObject = [
                'id'      => $this->job->getUniqueId('sqlclose'),
                'cont_id' => $this->correlationId,
                'type'    => 'sqlclose'
            ];
            return $this->executeQuery($queryObject);
        }
        elseif (!$this->correlationId)
            $this->state = QueryState::RUN_DONE;
    }

    /**
     * Retreive the correlation ID of the Query
     * @return string - correlation ID
     */
    public function getId(): string
    {
      return $this->correlationId;
    }

    /**
     * Retreive the current QueryState value
     * @return QueryState - current QueryState value
     */
    public function getState(): QueryState
    {
      return $this->state;
    }
}