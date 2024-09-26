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
     * @var $websocket_client
     */
    protected ?\Websocket\Client $websocket_client = null;

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
        $this->websocket_client = new \WebSocket\Client(uri: $this->genURI());
        $this->websocket_client->addHeader(name: "Authorization", content: "Basic " . $this->encodeCredentials());
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
            . "Websocket Client: $this->websocket_client" . PHP_EOL
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
     * @param string $dir directory containing the .env file (if any such file)
     * @return Client instance
     */
    public static function ClientFromEnv(string $dir = '.'): Client
    {
        $dotenv = Client::loadEnv(dir: $dir);
        $client = new Client(
            server: array_key_exists(key: 'MAPEPIRE_SERVER', array: $_ENV) ? $_ENV['MAPEPIRE_SERVER'] : "localhost",
            port: array_key_exists(key: 'MAPEPIRE_PORT', array: $_ENV) ? (int) $_ENV['MAPEPIRE_PORT'] : 8076,
            user: $_ENV['MAPEPIRE_DB_USER'],
            password: $_ENV['MAPEPIRE_DB_PASS']
        );
        $client->dotenv = $dotenv;
        return $client;
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
        $uri_string = "wss://$this->server:" . (string) $this->port . "/db/";
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
