<?php declare(strict_types=1);

/*
 * Copyright 2024 IBM
 * All Rights Reserved
 * Apache License 2.0 ... see LICENSE file
 * 
 * Original Author: Jack J. Woehr <jwoehr@softwoehr.com>
 * Modified By: Matthew Wiltzius <matthew.wiltzius@ibm.com>
 */

namespace Mapepire;

use Phrity\Net\Uri;
use WebSocket\Client;
// use WebSocket\Message\Message;
use WebSocket\Middleware\CloseHandler;
use WebSocket\Middleware\PingResponder;

require_once __DIR__ . '/types.php';

/**
 * SQLJob is a client to the Mapepire Server
 * @see https://mapepire-ibmi.github.io/
 * @see https://github.com/Mapepire-IBMi/mapepire-server
 */
class SQLJob
{
    private ?Client    $socket = null;
    private array      $options = [];
    private static int $uniqueIdCounter = 0;
    private JobStatus  $status = JobStatus::NotStarted;
    private bool       $isTracingChannelData = true;      // TODO: Tracing not yet enabled
    private string     $uniqueId;

    private ?string   $id = null;

    /**
     * Set up SQLJob object
     * @param DaemonServer|array|string|null $creds - Credentials to use for authentication or path to INI file
     * @param array|string|null              $options - additional options passed to the server, or INI section
     * @param string|null                    $section - section of INI to use for creds
     */
    public function __construct(DaemonServer|array|string|null $creds = null, 
                                array|string|null              $options = null, 
                                ?string                        $section = null)
    {
        if (is_array($options))
            $this->options = $options;
        elseif (is_string($options))
            $section = $options;

        $uniqueId = $this->getNewUniqueId("sqljob");
        if ($creds)
            $this->connect($creds, $section);
    }

    /**
     * Destructor for SQLJob object
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Getter to determine if socket exits
     */
    public function getSocket(): ?Client
    {
      return $this->socket;
    }

    /**
     * Generate a new unique id with an optional prefix
     * @param string $prefix - An optional prefix for the unique ID.
     * @return string - A unique ID.
     */
    public static function getNewUniqueId(string $prefix = 'id'): string
    {
        return $prefix . ++self::$uniqueIdCounter;
    }

    /**
     * Set up a websocket connection
     * @param DaemonServer $server - a DaemonServer object carrying authentication information
     * @return Client - the socket used to communicate with the server
     */
    public function getChannel(DaemonServer $server): Client
    {
        $socket = new Client(new Uri("wss://{$server->host}:{$server->port}/db/"));
        $socket->addHeader("Authorization", "Basic " . self::credentialEncoder($server->user, $server->password));
        $socket->addMiddleware(new CloseHandler());
        $socket->addMiddleware(new PingResponder());
        $verify = !$server->ignoreUnauthorized;
        $sslContext = [
            'verify_peer'       => $verify,
            'verify_peer_name'  => $verify,
        ];
        if ($verify && is_string($server->ca) && $server->ca !== '')
            $sslContext['cafile'] = $server->ca;

        $socket->setContext(["ssl" => $sslContext]);
        return $socket;
    }

    /**
     * Open channel and perform Mapepire protocol "connect".
     * @param DaemonServer|array|string $server - credentials used for authentication or path to INI file
     * @param string|null               $section - section of INI to use for creds
     * @return array - result of connection attempt 
     */
    public function connect(DaemonServer|array|string $server, ?string $section = null): array
    {
        $this->status = JobStatus::Connecting;
        if (is_array($server))
            $server = DaemonServer::fromArray($server);
        if (is_string($server))
            $server = DaemonServer::fromIni($server, $section);
        $this->socket = $this->getChannel($server);

        $props = implode(';', array_map(
            fn($k, $v) => $k . '=' . (is_array($v) ? implode(',', $v) : $v),
            array_keys($this->options),
            $this->options
         ));

        $connectionProps = [
            'id'          => self::getNewUniqueId(),
            'type'        => 'connect',
            'technique'   => 'tcp',
            'application' => 'PHP client',
            'props'       => strlen($props) > 0 ? $props : "",
        ];

        try {
            $this->send(json_encode($connectionProps));
            $rawResult = $this->receive();
        }
        catch (\Throwable $e) {
            $this->status = JobStatus::NotStarted;
            $this->close();
            throw new \RuntimeException('Failed during connect handshake', 0, $e);
        }
        $result = json_decode($rawResult, true) ?? [];

        if (array_key_exists("success", $result) && $result['success'])
        {
            $this->status = JobStatus::Ready;
        }
        else
        {
            $this->status = JobStatus::NotStarted;
            $this->close();
            $msg = isset($result['error']) ? (string)$result['error'] : 'Failed to connect to server.';
            throw new \RuntimeException($msg);
        }

        $this->id = $result["job"] ?? null;
        $this->isTracingChannelData = false;

        return $result; 
    }

    /**
     * Send a JSON message
     * @param string $message - the JSON message to the Mapepire server
     * @return void
     */
    public function send(string $message): void
    {
        if ($this->socket === null) 
            throw new \LogicException('Cannot send: not connected.');
    
        $this->status = JobStatus::Busy;
        try {
            $this->socket->text($message);
        } 
        catch (\Throwable $e) {
            $this->status = JobStatus::Ready;
            throw new \RuntimeException('send failed', 0, $e);
        }
        $this->status = JobStatus::Ready;
    }

    public function receive(): string
    {
        if (!$this->socket) { throw new \RuntimeException('Socket not connected'); }
        return $this->socket->receive()->getContent();
    }

    /**
     * Close the websocket connection
     * @return void
     */
    public function close(): void
    {
        if ($this->socket)
        {
            $this->socket->close();
            $this->socket = null;
            $this->status = JobStatus::Ended;
        }
    }

    public function query(string $sql, QueryOptions|array|null $options = null): Query
    {
        if(is_array($options)) {
            $options = new QueryOptions($options);
        }
        return new Query($this, $sql, $options);
    }

    public function queryAndRun(string $sql): string
    {
        return "Function not implemented: queryAndRun()";
    }

    public function getStatus(): JobStatus
    {
        return $this->status;
    }

    /**
     * base64 encode credentials for Basic Auth
     * @param string $user the user
     * @param string $password the password
     * @return string the encoded creds
     */
    private static function credentialEncoder(string $user, string $password): string
    {
        return base64_encode("$user:$password");
    }
}
