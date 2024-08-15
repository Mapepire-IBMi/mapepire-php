<?php

/*
 * Copyright 2024 Jack J. Woehr
 * jwoehr@softwoehr.com
 * PO Box 82 Beulah, Colorado 81023-8282
 * All Rights Reserved
 */

namespace Mapepire;

require_once 'vendor/autoload.php';

use \Amp\Websocket\Client\WebsocketHandshake;
use \Amp\Websocket\Client\WebsocketConnection;

/**
 * Client to Mapepire Server
 * @see https://mapepire-ibmi.github.io/
 * @see https://github.com/Mapepire-IBMi/mapepire-server
 */
class Client implements \Stringable
{

    /**
     * DNS or IP of Mapepire server
     * @var ?string
     */
    protected ?string $server = null;
    /**
     * port  of Mapepire server
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
     * @var ?WebsocketConnection
     */
    protected ?WebsocketConnection $connection = null;

    /**
     * ctor takes server port user password
     * @param string $server mapepire server dns or ipaddr
     * @param int $port mapepire server port
     * @param string $user user for authorization to IBM i Db2
     * @param string $password password for authorization to IBM i Db2
     */
    public function __construct(string $server, int $port, string $user, string $password)
    {
        $this->server = $server;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * @override
     * @return string String representation of Client instance
     */
    public function __toString(): string
    {
        $result = "\Mapepire\Client" . PHP_EOL
            . "Server: $this->server" . PHP_EOL
            . "Port: $this->port" . PHP_EOL
            . "User: $this->user" . PHP_EOL
            . "Connection: $this->connection" . PHP_EOL
        ;
        return $result;
    }

    /**
     * Instance a Client from environment variables, typically a .env file.
     * Loads the dotenv object and stores it in the created instance.
     * Chooses defaults if the variables do not appear in the $_ENV.
     * - MAPEPIRE_SERVER localhost
     * - MAPEPIRE_PORT 8076
     * No defaults for
     * - MAPEPIRE_DB_USER
     * - MAPEPIRE_DB_PASS
     * See the .env.sample in the root of the project
     * @param string $dir directory containing the .env file if any
     * @return \Mapepire\Client instance
     */
    public static function ClientFromEnv(string $dir = __DIR__): Client
    {
        $dotenv = Client::loadEnv($dir);
        $client = new Client(
            array_key_exists('MAPEPIRE_SERVER', $_ENV) ? $_ENV['MAPEPIRE_SERVER'] : "localhost",
            array_key_exists('MAPEPIRE_PORT', $_ENV) ? (int) $_ENV['MAPEPIRE_PORT'] : 8076,
            $_ENV['MAPEPIRE_DB_USER'],
            $_ENV['MAPEPIRE_DB_PASS']
        );
        $client->dotenv = $dotenv;
        return $client;
    }

    /**
     * Load the .env file if any
     * @param string $dir Directory .env file found in, default `__DIR__`
     * @return object the dotenv object
     */
    protected static function loadEnv(string $dir = __DIR__): object
    {
        $dotenv = \Dotenv\Dotenv::createImmutable($dir);
        $dotenv->safeLoad();
        return $dotenv;
    }

    /**
     * Connect the websocket
     * WON'T WORK YET until we factory the authorized connection
     * @return \Amp\Websocket\Client\WebsocketConnection
     */
    public function connect(): WebsocketConnection
    {
        $creds = $this->encodeCredentials();
        $handshake = (new WebsocketHandshake($this->genURI()))->withHeader("Authorization:Basic ", $creds);
        $this->connection = \Amp\Websocket\Client\connect($handshake);
        return $this->connection;
    }

    /**
     * Formulate the URI for the connection
     * @return string the uri
     */
    private function genURI(): string
    {
        $uri = "wss://$this->server:" . (string) $this->port . "/db/";
        return $uri;
    }

    private function encodeCredentials(): string
    {
        $credentials = base64_encode("$this->user:$this->password");
        return $credentials;

    }
}
