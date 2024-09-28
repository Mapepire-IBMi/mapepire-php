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
    const DEFAULT_HOSTNAME = 'localhost';
    const DEFAULT_PORT = 8076;
    const DEFAULT_IGNORE_UNAUTHORIZED = false;
    const DEFAULT_VERIFY_HOSTNAME = true;
    const DEFAULT_TIMEOUT = 60;
    const DEFAULT_FRAMESIZE = 4096;
    const DEFAULT_PERSISTENCE = false;

    /**
     * Summary of __construct
     * @param string $host Mapepire server host
     * @param int $port Mapepire server port
     * @param string $user Userid to authorize on Mapepire server
     * @param string $password Password to authorize on Mapepire server
     * @param bool $ignoreUnauthorized .IFF. true ignore self-signed, default false
     * @param bool $verifyHostName .IFF. true verify the host name
     */
    public function __construct(
        string $host = self::DEFAULT_HOSTNAME,
        int $port = self::DEFAULT_PORT,
        string $user = null,
        string $password = null,
        bool $ignoreUnauthorized = self::DEFAULT_IGNORE_UNAUTHORIZED,
        bool $verifyHostName = self::DEFAULT_VERIFY_HOSTNAME,
        int $timeout = self::DEFAULT_TIMEOUT,
        int $framesize = self::DEFAULT_FRAMESIZE,
        bool $persistent = self::DEFAULT_PERSISTENCE
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
            . "verifyHostName: $this->verifyHostName" . PHP_EOL
            . "timeout: $this->timeout" . PHP_EOL
            . "framesize: $this->framesize" . PHP_EOL
            . "persistent: $this->persistent" . PHP_EOL
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
     *   - MAPEPIRE_VERIFY_HOST_NAME true
     * No defaults for
     * - MAPEPIRE_DB_USER
     * - MAPEPIRE_DB_PASS
     * See the .env.sample in the root of the project
     * @param string $dir directory containing the .env file (if any such file)
     * @return DaemonServer instance
     */
    public static function DaemonServerFromDotEnv(string $dir = '.'): DaemonServer
    {
        self::loadDotEnv(dir: $dir);
        $DaemonServer = new DaemonServer(
            host: array_key_exists(key: 'MAPEPIRE_SERVER', array: $_ENV) ? $_ENV['MAPEPIRE_SERVER'] : self::DEFAULT_HOSTNAME,
            port: array_key_exists(key: 'MAPEPIRE_PORT', array: $_ENV) ? (int) $_ENV['MAPEPIRE_PORT'] : self::DEFAULT_PORT,
            user: $_ENV['MAPEPIRE_DB_USER'],
            password: $_ENV['MAPEPIRE_DB_PASS'],
            ignoreUnauthorized: array_key_exists(key: 'MAPEPIRE_IGNORE_UNAUTHORIZED', array: $_ENV)
            ? strtolower(string: $_ENV['MAPEPIRE_IGNORE_UNAUTHORIZED']) == 'true'
            : self::DEFAULT_IGNORE_UNAUTHORIZED,
            verifyHostName: array_key_exists(key: 'MAPEPIRE_VERIFY_HOSTNAME', array: $_ENV)
            ? strtolower(string: $_ENV['MAPEPIRE_VERIFY_HOSTNAME']) == 'true'
            : self::DEFAULT_VERIFY_HOSTNAME,
            timeout: array_key_exists(key: 'MAPEPIRE_TIMEOUT', array: $_ENV) ? (int) $_ENV['MAPEPIRE_TIMEOUT'] : self::DEFAULT_TIMEOUT,
            framesize: array_key_exists(key: 'MAPEPIRE_FRAMESIZE', array: $_ENV) ? (int) $_ENV['MAPEPIRE_FRAMESIZE'] : self::DEFAULT_FRAMESIZE,
            persistent: array_key_exists(key: 'MAPEPIRE_PERSISTENCE', array: $_ENV) ? (int) $_ENV['MAPEPIRE_DEFAULT_PERSISTENCE'] : self::DEFAULT_PERSISTENCE
        );
        return $DaemonServer;
    }
    public ?string $host;
    public ?int $port;
    public ?string $user;
    public ?string $password;
    public ?bool $ignoreUnauthorized;
    public ?bool $verifyHostName;
    public ?int $timeout; // default 60 seconds
    public ?int $framesize; // default 4096 bytes
    public ?bool $persistent;    // If client should attempt persistent connection
}
