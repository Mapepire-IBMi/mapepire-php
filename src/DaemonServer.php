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
 * DaemonServer structure represents factors to initialize a SQLJob instance.
 */
class DaemonServer implements \Stringable
{
    /**
     * Summary of __construct
     * @param string $host Mapepire server host
     * @param int $port Mapepire server port
     * @param string $user Userid to authorize on Mapepire server
     * @param string $password Password to authorize on Mapepire server
     * @param bool $ignoreUnauthorized .IFF. true ignore self-signed, default false
     */
    public function __construct(string $host, int $port, string $user, string $password, bool $ignoreUnauthorized = false)
    {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
        $this->ignoreUnauthorized = $ignoreUnauthorized;
    }

    /**
     * @override
     * @return string String representation of DaemonServer instance
     */
    public function __toString(): string
    {
        $result = "Mapepire\DaemonServer" . PHP_EOL
            . "host: $this->host" . PHP_EOL
            . "port: $this->port" . PHP_EOL
            . "user: $this->user" . PHP_EOL
            . "password: " . ($this->password ? "(hidden)" : "(no password was provided)") . PHP_EOL
            . "ignoreUnauthorized: $this->ignoreUnauthorized" . PHP_EOL
        ;
        return $result;
    }

    /**
     * Load the .env file if any
     * @param string $dir Directory .env file found in, default '.'
     * @return object the dotenv object
     */
    public static function loadDotEnv(string $dir = '.'): object
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(paths: $dir);
        $dotenv->safeLoad();
        return $dotenv;
    }

    /**
     * Instance a DaemonServer structure from a .env file.
     * - Loads the dotenv object.
     * - Chooses defaults if the variables do not appear in the $_ENV.
     *   - MAPEPIRE_SERVER localhost
     *   - MAPEPIRE_PORT 8076
     *   - MAPEPIRE_IGNORE_UNAUTHORIZED false
     * No defaults for
     * - MAPEPIRE_DB_USER
     * - MAPEPIRE_DB_PASS
     * See the .env.sample in the root of the project
     * @param string $dir directory containing the .env file (if any such file)
     * @return DaemonServer instance
     */
    public static function DaemonServerFromDotEnv(string $dir = '.'): DaemonServer
    {
        $dotenv = self::loadDotEnv(dir: $dir);
        $DaemonServer = new DaemonServer(
            host: array_key_exists(key: 'MAPEPIRE_SERVER', array: $_ENV) ? $_ENV['MAPEPIRE_SERVER'] : "localhost",
            port: array_key_exists(key: 'MAPEPIRE_PORT', array: $_ENV) ? (int) $_ENV['MAPEPIRE_PORT'] : 8076,
            user: $_ENV['MAPEPIRE_DB_USER'],
            password: $_ENV['MAPEPIRE_DB_PASS'],
            ignoreUnauthorized: array_key_exists(key: 'MAPEPIRE_IGNORE_UNAUTHORIZED', array: $_ENV)
            ? strtolower(string: $_ENV['MAPEPIRE_IGNORE_UNAUTHORIZED']) == 'true'
            : false,
        );
        return $DaemonServer;
    }
    public ?string $host;
    public ?int $port;
    public ?string $user;
    public ?string $password;
    public ?bool $ignoreUnauthorized;
}