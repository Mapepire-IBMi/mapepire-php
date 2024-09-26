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
     * dotenv object if any
     * @var ?object
     */
    protected ?object $dotenv = null;

    /**
     * The connection object
     * @var $websocket_client
     */
    protected ?\Websocket\Client $websocket_client = null;

    /**
     * ctor takes host port user password
     * @param string $host mapepire host dns or ipaddr
     * @param int $port mapepire host port
     * @param string $user user for authorization to IBM i Db2
     * @param string $password password for authorization to IBM i Db2
     */
    public function __construct(string $host, int $port, string $user, string $password)
    {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
        $this->websocket_client = new \Websocket\Client(uri: $this->genURI());
        $this->websocket_client->addHeader(name: "Authorization", content: "Basic " . $this->encodeCredentials());
    }

    /**
     * @override
     * @return string String representation of SQLJob instance
     */
    public function __toString(): string
    {
        $result = "\Mapepire\SQLJob" . PHP_EOL
            . "host: $this->host" . PHP_EOL
            . "port: $this->port" . PHP_EOL
            . "user: $this->user" . PHP_EOL
            . "Websocket\Client: $this->websocket_client" . PHP_EOL
        ;
        return $result;
    }

    /**
     * Instance a SQLJob from environment variables, typically a .env file.
     * Loads the dotenv object and stores it in the created instance.
     * Chooses defaults if the variables do not appear in the $_ENV.
     * - MAPEPIRE_host localhost
     * - MAPEPIRE_PORT 8076
     * No defaults for
     * - MAPEPIRE_DB_USER
     * - MAPEPIRE_DB_PASS
     * See the .env.sample in the root of the project
     * @param string $dir directory containing the .env file (if any such file)
     * @return SQLJob instance
     */
    public static function SQLJobFromEnv(string $dir = '.'): SQLJob
    {
        $dotenv = SQLJob::loadEnv(dir: $dir);
        $SQLJob = new SQLJob(
            host: array_key_exists(key: 'MAPEPIRE_SERVER', array: $_ENV) ? $_ENV['MAPEPIRE_SERVER'] : "localhost",
            port: array_key_exists(key: 'MAPEPIRE_PORT', array: $_ENV) ? (int) $_ENV['MAPEPIRE_PORT'] : 8076,
            user: $_ENV['MAPEPIRE_DB_USER'],
            password: $_ENV['MAPEPIRE_DB_PASS']
        );
        $SQLJob->dotenv = $dotenv;
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
        array $sslContext = ["verify_peer" => false, "verify_peer_name" => false,]
    ): object {
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
}