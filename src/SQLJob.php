<?php

/*
 * Copyright 2024 IBM
 * All Rights Reserved
 * Apache License 2.0 ... see LICENSE file
 * Author: Jack J. Woehr
 * jwoehr@softwoehr.com
 */

namespace Mapepire;

require_once 'vendor/autoload.php';

/**
 * SQLJob is a client to the Mapepire Server
 * @see https://mapepire-ibmi.github.io/
 * @see https://github.com/Mapepire-IBMi/mapepire-server
 */
class SQLJob implements \Stringable
{
    /**
     * Ctor, all defaults identified as constants in \Mapepire\DaemonServer
     * @param string $host mapepire host dns or ipaddr
     * @param int $port mapepire host port
     * @param string $user user for authorization to IBM i Db2
     * @param string $password password for authorization to IBM i Db2
     * @param bool $ignoreUnauthorized .IFF. true allow snakeoil cert
     * @param bool $verifyHostName .IFF. true verify hostname
     * @param int $timeout timeout in seconds
     * @param int $framesize frame size
     * @param bool $persistent try for persistent connection .IFF. true
     */
    public function __construct(
        string $host = DaemonServer::DEFAULT_HOSTNAME,
        int $port = DaemonServer::DEFAULT_PORT,
        string $user = null,
        string $password = null,
        bool $ignoreUnauthorized = DaemonServer::DEFAULT_IGNORE_UNAUTHORIZED,
        bool $verifyHostName = DaemonServer::DEFAULT_VERIFY_HOSTNAME,
        int $timeout = DaemonServer::DEFAULT_TIMEOUT,
        int $framesize = DaemonServer::DEFAULT_FRAMESIZE,
        bool $persistent = DaemonServer::DEFAULT_PERSISTENCE
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
        $this->ignoreUnauthorized = $ignoreUnauthorized;
        $this->verifyHostName = $verifyHostName;
        $this->timeout = $timeout;
        $this->framesize = $framesize;
        $this->persistent = $persistent;
        $this->websocket_client = new \WebSocket\Client(uri: $this->genURI());
        $this->websocket_client->addHeader(name: "Authorization", content: "Basic " . $this->encodeCredentials());
        $this->websocket_client->addMiddleware(middleware: new \WebSocket\Middleware\CloseHandler());
        $this->websocket_client->addMiddleware(middleware: new \WebSocket\Middleware\PingResponder());
        $this->websocket_client->setTimeout = $this->timeout;
        $this->websocket_client->setFrameSize = $this->framesize;
        $this->websocket_client->setPersistent = $this->persistent;
    }

    /**
     * @override
     * @return string String representation of SQLJob instance
     */
    public function __toString(): string
    {
        $result = "Mapepire\SQLJob" . PHP_EOL
            . "host: $this->host" . PHP_EOL
            . "port: $this->port" . PHP_EOL
            . "user: $this->user" . PHP_EOL
            . "ignoreUnauthorized: $this->ignoreUnauthorized" . PHP_EOL
            . "verifyHostName: $this->verifyHostName" . PHP_EOL
            . "timeout: $this->timeout" . PHP_EOL
            . "framesize: $this->framesize" . PHP_EOL
            . "persistent: $this->persistent" . PHP_EOL
            . "Websocket\Client: $this->websocket_client" . PHP_EOL
        ;
        return $result;
    }

    /**
     * Instance a SQLJob from environment variables, typically a .env file.
     * Loads the dotenv object and stores it in the created instance.
     * Chooses defaults if the variables do not appear in the $_ENV.
     * All the defaults appear as constants in Mapepire\DaemonServer.
     *   - MAPEPIRE_host localhost
     *   - MAPEPIRE_PORT 8076
     *   - MAPEPIRE_IGNORE_UNAUTHORIZED false
     *   - MAPEPIRE_VERIFY_HOSTNAME true
     *   - MAPEPIRE_TIMEOUT 60 seconds
     *   - MAPEPIRE_FRAMESIZE 4096
     *   - MAPEPIRE_PERSISTENCE true
     * No defaults for
     *   - MAPEPIRE_DB_USER
     *   - MAPEPIRE_DB_PASS
     * See the .env.sample in the root of the project
     * @param string $dir directory containing the .env file (if any such file)
     * @return SQLJob instance
     */
    public static function SQLJobFromEnv(string $dir = '.'): SQLJob
    {
        $dotenv = SQLJob::loadEnv(dir: $dir);
        $sqlJob = new SQLJob(
            host: array_key_exists(key: 'MAPEPIRE_SERVER', array: $_ENV) ? $_ENV['MAPEPIRE_SERVER'] : DaemonServer::DEFAULT_HOSTNAME,
            port: array_key_exists(key: 'MAPEPIRE_PORT', array: $_ENV) ? (int) $_ENV['MAPEPIRE_PORT'] : DaemonServer::DEFAULT_PORT,
            user: $_ENV['MAPEPIRE_DB_USER'],
            password: $_ENV['MAPEPIRE_DB_PASS'],
            ignoreUnauthorized: array_key_exists(key: 'MAPEPIRE_IGNORE_UNAUTHORIZED', array: $_ENV)
            ? strtolower(string: $_ENV['MAPEPIRE_IGNORE_UNAUTHORIZED']) == 'true'
            : DaemonServer::DEFAULT_IGNORE_UNAUTHORIZED,
            verifyHostName: array_key_exists(key: 'MAPEPIRE_VERIFY_HOSTNAME', array: $_ENV)
            ? strtolower(string: $_ENV['MAPEPIRE_VERIFY_HOSTNAME']) == 'true'
            : DaemonServer::DEFAULT_VERIFY_HOSTNAME,
            timeout: array_key_exists(key: 'MAPEPIRE_TIMEOUT', array: $_ENV) ? (int) $_ENV['MAPEPIRE_TIMEOUT'] : DaemonServer::DEFAULT_TIMEOUT,
            framesize: array_key_exists(key: 'MAPEPIRE_FRAMESIZE', array: $_ENV) ? (int) $_ENV['MAPEPIRE_FRAMESIZE'] : DaemonServer::DEFAULT_FRAMESIZE,
            persistent: array_key_exists(key: 'MAPEPIRE_PERSISTENCE', array: $_ENV) ? (int) $_ENV['MAPEPIRE_DEFAULT_PERSISTENCE'] : DaemonServer::DEFAULT_PERSISTENCE
        );
        $sqlJob->dotenv = $dotenv;
        return $sqlJob;
    }

    /**
     * Instance a SQLJob from a DaemonServer instance
     */
    public static function SQLJobFromDaemonServer(DaemonServer $daemonServer): SQLJob
    {
        $SQLJob = new SQLJob(
            host: $daemonServer->host,
            port: $daemonServer->port,
            user: $daemonServer->user,
            password: $daemonServer->password,
            ignoreUnauthorized: $daemonServer->ignoreUnauthorized,
            verifyHostName: $daemonServer->verifyHostName,
            timeout: $daemonServer->timeout,
            framesize: $daemonServer->framesize,
            persistent: $daemonServer->persistent
        );
        return $SQLJob;
    }

    /**
     * Load the .env file if any
     * @param string $dir Directory .env file found in, default '.'
     * @return object the dotenv object
     */
    public static function loadEnv(string $dir = '.'): object
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(paths: $dir);
        $dotenv->safeLoad();
        return $dotenv;
    }

    /**
     * Send message and receive response.
     * Connects if not currently connected.
     * @param string $message the JSON message to the Mapepire server
     * @param mixed $sslContext the specific SSL Context. If none, uses the context created by ctor.
     * @return \WebSocket\Message\Text the response text object .. ->getContent() to get the JSON message content
     */
    public function singleSendAndReceive(
        string $message,
        ?array $sslContext = null,
    ): \WebSocket\Message\Text {
        $sslContext = $sslContext ?: ["verify_peer" => !$this->ignoreUnauthorized, "verify_peer_name" => $this->verifyHostName,];
        $this->websocket_client->setContext(context: ["ssl" => $sslContext]);
        $this->websocket_client->text(message: $message);
        return $this->websocket_client->receive();
    }

    /**
     * Close the websocket connection
     * @return void
     */
    public function close(): void
    {
        $this->websocket_client->close();
    }

    /**
     * Formulate the URI for the connection
     * @return string the uri
     */
    private function genURI(): \Phrity\Net\Uri
    {
        $uri_string = "wss://$this->host:" . (string) $this->port . "/db/";
        return new \Phrity\Net\Uri(uri_string: $uri_string);
    }

    /**
     * base64 encode credentials for Basic Auth
     * @param string $user the user
     * @param string $password the password
     * @return string the encoded creds
     */
    public static function credentialEncoder(string $user, string $password): string
    {
        return base64_encode(string: "$user:$password");
    }

    /**
     * Encode specific credentials stored in this.
     * @return string encoded creds
     */
    private function encodeCredentials(): string
    {
        return self::credentialEncoder(user: $this->user, password: $this->password);
    }

    /**
     * Get the value of host
     */
    public function getHost(): ?string
    {
        return $this->host;
    }

    /**
     * Set the value of host
     */
    public function setHost(?string $host): self
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get the value of port
     */
    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * Set the value of port
     */
    public function setPort(?int $port): self
    {
        $this->port = $port;

        return $this;
    }

    /**
     * Get the value of user
     */
    public function getUser(): ?string
    {
        return $this->user;
    }

    /**
     * Set the value of user
     */
    public function setUser(?string $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get the value of password
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Set the value of password
     */
    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get the value of ignoreUnauthorized
     */
    public function isIgnoreUnauthorized(): bool
    {
        return $this->ignoreUnauthorized;
    }

    /**
     * Set the value of ignoreUnauthorized
     */
    public function setIgnoreUnauthorized(bool $ignoreUnauthorized): self
    {
        $this->ignoreUnauthorized = $ignoreUnauthorized;

        return $this;
    }

    /**
     * Get the value of dotenv
     */
    public function getDotenv(): ?object
    {
        return $this->dotenv;
    }

    /**
     * Set the value of dotenv
     */
    public function setDotenv(?object $dotenv): self
    {
        $this->dotenv = $dotenv;

        return $this;
    }

    /**
     * Get the value of websocket_client
     */
    public function getWebsocketClient(): ?\WebSocket\Client
    {
        return $this->websocket_client;
    }

    /**
     * Set the value of websocket_client
     */
    public function setWebsocketClient(?\WebSocket\Client $websocket_client): self
    {
        $this->websocket_client = $websocket_client;

        return $this;
    }

    /**
     * Get the value of verifyHostName
     */
    public function isVerifyHostName(): bool
    {
        return $this->verifyHostName;
    }

    /**
     * Set the value of verifyHostName
     */
    public function setVerifyHostName(bool $verifyHostName): self
    {
        $this->verifyHostName = $verifyHostName;

        return $this;
    }

    /**
     * Get the value of timeout
     */
    public function getTimeout(): ?int
    {
        return $this->timeout;
    }

    /**
     * Set the value of timeout
     */
    public function setTimeout(?int $timeout): self
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * Get the value of framesize
     */
    public function getFramesize(): ?int
    {
        return $this->framesize;
    }

    /**
     * Set the value of framesize
     */
    public function setFramesize(?int $framesize): self
    {
        $this->framesize = $framesize;

        return $this;
    }

    /**
     * Get the value of persistent
     */
    public function isPersistent(): ?bool
    {
        return $this->persistent;
    }

    /**
     * Set the value of persistent
     */
    public function setPersistent(?bool $persistent): self
    {
        $this->persistent = $persistent;

        return $this;
    }
    /**
     * DNS or IP of Mapepire server
     * @var ?string
     */
    protected ?string $host = null;
    /**
     * port number of Mapepire host
     * @var ?int
     */
    protected ?int $port = null;
    /**
     * User profile for IBM i Db2
     * @var ?string
     */
    protected ?string $user = null;
    /**
     * Password for IBM i Db2
     * @var ?string
     */
    private ?string $password = null;
    /**
     * ignoreUnauthorized .IFF. true accept snakeoil cert
     * @var bool
     */
    private ?bool $ignoreUnauthorized = false;
    /**
     * verifyHostName .IFF. true
     * @var ?bool
     */
    private ?bool $verifyHostName = true;
    /**
     * Connection timeout
     * @var ?int
     */
    private ?int $timeout; // default 60 seconds
    /**
     * Communication frame size
     * @var ?int
     */
    private ?int $framesize; // default 4096 bytes
    /**
     * Should attempt connection persistent
     * @var ?bool
     */
    private ?bool $persistent;    // If client should attempt persistent connection
    /**
     * dotenv object if any
     * @var ?object
     */
    private ?object $dotenv = null;
    /**
     * The connection object
     * @var $websocket_client
     */
    private ?\WebSocket\Client $websocket_client = null;

}
