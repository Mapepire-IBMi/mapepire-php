<?php

/*
 * Copyright 2024 IBM
 * All Rights Reserved
 * Apache License 2.0 ... see LICENSE file
 * Author: Jack J. Woehr
 * jwoehr@softwoehr.com
 */

namespace Mapepire;

/**
 * DaemonServer structure represents factors to initialize a SQLJob instance.
 */
class DaemonServer implements \Stringable
{
    const DEFAULT_HOST_NAME = 'localhost';
    const DEFAULT_PORT = 8076;
    const DEFAULT_VERIFY_HOST_CERT = true;
    const DEFAULT_VERIFY_HOST_NAME = true;
    const DEFAULT_TIMEOUT = 60;
    const DEFAULT_FRAMESIZE = 4096;
    const DEFAULT_PERSISTENCE = false;

    /**
     * Summary of __construct
     * @param string $host Mapepire server host
     * @param int $port Mapepire server port
     * @param string $user Userid to authorize on Mapepire server
     * @param string $password Password to authorize on Mapepire server
     * @param bool $verifyHostCert .IFF. false accept self-signed, default true
     * @param bool $verifyHostName .IFF. true verify the host name
     */
    public function __construct(
        string $host = self::DEFAULT_HOST_NAME,
        int $port = self::DEFAULT_PORT,
        string $user = null,
        string $password = null,
        bool $verifyHostCert = self::DEFAULT_VERIFY_HOST_CERT,
        bool $verifyHostName = self::DEFAULT_VERIFY_HOST_NAME,
        int $timeout = self::DEFAULT_TIMEOUT,
        int $framesize = self::DEFAULT_FRAMESIZE,
        bool $persistent = self::DEFAULT_PERSISTENCE
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
        $this->verifyHostCert = $verifyHostCert;
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
            . "verifyHostCert: $this->verifyHostCert" . PHP_EOL
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
     *   - MAPEPIRE_VERIFY_HOST_CERT true
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
            host: array_key_exists(key: 'MAPEPIRE_SERVER', array: $_ENV) ? $_ENV['MAPEPIRE_SERVER'] : self::DEFAULT_HOST_NAME,
            port: array_key_exists(key: 'MAPEPIRE_PORT', array: $_ENV) ? (int) $_ENV['MAPEPIRE_PORT'] : self::DEFAULT_PORT,
            user: $_ENV['MAPEPIRE_DB_USER'],
            password: $_ENV['MAPEPIRE_DB_PASS'],
            verifyHostCert: array_key_exists(key: 'MAPEPIRE_VERIFY_HOST_CERT', array: $_ENV)
            ? strtolower(string: $_ENV['MAPEPIRE_VERIFY_HOST_CERT']) == 'true'
            : self::DEFAULT_VERIFY_HOST_CERT,
            verifyHostName: array_key_exists(key: 'MAPEPIRE_VERIFY_HOST_NAME', array: $_ENV)
            ? strtolower(string: $_ENV['MAPEPIRE_VERIFY_HOST_NAME']) == 'true'
            : self::DEFAULT_VERIFY_HOST_NAME,
            timeout: array_key_exists(key: 'MAPEPIRE_TIMEOUT', array: $_ENV) ? (int) $_ENV['MAPEPIRE_TIMEOUT'] : self::DEFAULT_TIMEOUT,
            framesize: array_key_exists(key: 'MAPEPIRE_FRAMESIZE', array: $_ENV) ? (int) $_ENV['MAPEPIRE_FRAMESIZE'] : self::DEFAULT_FRAMESIZE,
            persistent: array_key_exists(key: 'MAPEPIRE_PERSISTENCE', array: $_ENV) ? (int) $_ENV['MAPEPIRE_DEFAULT_PERSISTENCE'] : self::DEFAULT_PERSISTENCE
        );
        return $DaemonServer;
    }
    /**
     * DNS or IP of Mapepire server
     * @var ?string
     */
    public ?string $host;
    /**
     * port number of Mapepire host
     * @var ?int
     */
    public ?int $port;
    /**
     * User profile for IBM i Db2
     * @var ?string
     */
    public ?string $user;
    /**
     * Password for IBM i Db2
     * @var ?string
     */
    public ?string $password;
    /**
     * verifyHostCert .IFF. false accept snakeoil cert
     * @var bool
     */
    public ?bool $verifyHostCert;
    /**
     * verifyHostName .IFF. true
     * @var ?bool
     */
    public ?bool $verifyHostName;
    /**
     * Connection timeout
     * @var ?int
     */
    public ?int $timeout; // default 60 seconds
    /**
     * Communication frame size
     * @var ?int
     */
    public ?int $framesize; // default 4096 bytes
    /**
     * Should attempt connection persistent
     * @var ?bool
     */
    public ?bool $persistent;    // If client should attempt persistent connection
}
