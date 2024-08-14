<?php

/*
 * Copyright 2024 Jack J. Woehr
 * jwoehr@softwoehr.com
 * PO Box 82 Beulah, Colorado 81023-8282
 * All Rights Reserved
 */

namespace Mapepire;
require_once 'vendor/autoload.php';
class Client implements \Stringable
{

    protected ?string $server = null;
    protected ?int $port = null;
    protected ?string $user = null;
    private ?string $password = null;
    protected ?object $dotenv = null;

    public function __construct(string $server, int $port, string $user, string $password)
    {
        $this->server = $server;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
    }
    public function __toString(): string
    {
        $result = "\Mapepire\Client" . PHP_EOL
            . "Server: $this->server" . PHP_EOL
            . "Port: $this->port" . PHP_EOL
            . "User: $this->user" . PHP_EOL
        ;
        return $result;
    }

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

    protected static function loadEnv(string $dir = __DIR__): object
    {
        $dotenv = \Dotenv\Dotenv::createImmutable($dir);
        $dotenv->safeLoad();
        return $dotenv;
    }
}
