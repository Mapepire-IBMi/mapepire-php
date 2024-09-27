<?php

/*
 * Copyright 2024 Jack J. Woehr
 * jwoehr@softwoehr.com
 * PO Box 82 Beulah, Colorado 81023-8282
 * All Rights Reserved
 * Apache License 2.0 ... see LICENSE file
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
    private bool $ignoreUnauthorized = false;
    /**
     * verifyHostName .IFF. true
     * @var ?object
     */
    private bool $verifyHostName = true;
    /**
     * dotenv object if any
     * @var ?object
     */
    protected ?object $dotenv = null;

    /**
     * The connection object
     * @var $websocket_client
     */
    private ?\WebSocket\Client $websocket_client = null;

    /**
     * ctor takes host port user password and optionally a flag
     * to accept self-signed certificates
     * @param string $host mapepire host dns or ipaddr
     * @param int $port mapepire host port
     * @param string $user user for authorization to IBM i Db2
     * @param string $password password for authorization to IBM i Db2
     * @param bool $ignoreUnauthorized .IFF. true allow snakeoil cert
     */
    public function __construct(string $host, int $port, string $user, string $password, bool $ignoreUnauthorized = false, bool $verifyHostName)
    {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
        $this->ignoreUnauthorized = $ignoreUnauthorized;
        $this->verifyHostName = $verifyHostName;
        $this->websocket_client = new \WebSocket\Client(uri: $this->genURI());
        $this->websocket_client->addHeader(name: "Authorization", content: "Basic " . $this->encodeCredentials());
        $this->websocket_client->addMiddleware(middleware: new \WebSocket\Middleware\CloseHandler());
        $this->websocket_client->addMiddleware(middleware: new \WebSocket\Middleware\PingResponder());
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
            . "Websocket\Client: $this->websocket_client" . PHP_EOL
        ;
        return $result;
    }

    /**
     * Instance a SQLJob from environment variables, typically a .env file.
     * Loads the dotenv object and stores it in the created instance.
     * Chooses defaults if the variables do not appear in the $_ENV.
     *   - MAPEPIRE_host localhost
     *   - MAPEPIRE_PORT 8076
     *   - MAPEPIRE_IGNORE_UNAUTHORIZED false
     * No defaults for
     *   - MAPEPIRE_DB_USER
     * - MAPEPIRE_DB_PASS
     * See the .env.sample in the root of the project
     * @param string $dir directory containing the .env file (if any such file)
     * @return SQLJob instance
     */
    public static function SQLJobFromEnv(string $dir = '.'): SQLJob
    {
        $dotenv = SQLJob::loadEnv(dir: $dir);
        $sqlJob = new SQLJob(
            host: array_key_exists(key: 'MAPEPIRE_SERVER', array: $_ENV) ? $_ENV['MAPEPIRE_SERVER'] : "localhost",
            port: array_key_exists(key: 'MAPEPIRE_PORT', array: $_ENV) ? (int) $_ENV['MAPEPIRE_PORT'] : 8076,
            user: $_ENV['MAPEPIRE_DB_USER'],
            password: $_ENV['MAPEPIRE_DB_PASS'],
            ignoreUnauthorized: array_key_exists(key: 'MAPEPIRE_IGNORE_UNAUTHORIZED', array: $_ENV) ? strtolower(string: $_ENV['MAPEPIRE_IGNORE_UNAUTHORIZED']) == 'true'
            : false,
            verifyHostName: array_key_exists(key: 'MAPEPIRE_VERIFY_HOST_NAME', array: $_ENV) ? strtolower(string: $_ENV['MAPEPIRE_IGNORE_UNAUTHORIZED']) == 'true'
            : false,
        );
        $sqlJob->dotenv = $dotenv;
        return $sqlJob;
    }

    public static function SQLJobFromDaemonServer(DaemonServer $daemonServer): SQLJob
    {
        $SQLJob = new SQLJob(
            host: $daemonServer->host,
            port: $daemonServer->port,
            user: $daemonServer->user,
            password: $daemonServer->password,
            ignoreUnauthorized: $daemonServer->ignoreUnauthorized,
            verifyHostName: $daemonServer->verifyHostName,
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

    public function singleSendAndReceive(
        string $message,
        ?array $sslContext = null,
    ): object {
        $sslContext = $sslContext ?: ["verify_peer" => !$this->ignoreUnauthorized, "verify_peer_name" => $this->verifyHostName,];
        $this->websocket_client->setContext(context: ["ssl" => $sslContext]);
        $this->websocket_client->text(message: $message);
        return $this->websocket_client->receive();
    }

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

    public static function credentialEncoder(string $user, string $password): string
    {
        return base64_encode(string: "$user:$password");
    }

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
}
