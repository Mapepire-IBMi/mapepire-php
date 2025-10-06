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

final class DaemonServer
{
    public function __construct(
        public readonly string  $host,
        public readonly string  $user,
        public readonly string  $password,
        public readonly int     $port = 8076,
        public readonly bool    $ignoreUnauthorized = false,
        public readonly ?string $ca = null,
    ) {
        // Basic validation
        if ($this->host === '') {
            throw new \InvalidArgumentException('host must be a non-empty string.');
        }
        if ($this->user === '') {
            throw new \InvalidArgumentException('user must be a non-empty string.');
        }
        if ($this->password === '') {
            throw new \InvalidArgumentException('password must be a non-empty string.');
        }
        if ($this->port < 1 || $this->port > 65535) {
            throw new \InvalidArgumentException('port must be between 1 and 65535.');
        }
        if (is_string($this->ca) && $this->ca !== '' && !is_readable($this->ca)) {
            throw new \InvalidArgumentException("CA file is not readable: {$this->ca}");
        }
    }

    /**
     * Create a new DaemonServer object from an associative array
     * @param array $data - associate array containing:
     *                            host (string, required)
     *                            user (string, required)
     *                            password (string, required)
     *                            port (int, optional)
     *                            ignoreUnauthorized (bool, optional)
     *                            ca (string, optional)
     * @return self - A new DaemonServer object
     */
    public static function fromArray(array $data): self
    {
        if (!isset($data['host'], $data['user'], $data['password']))
            throw new \InvalidArgumentException("host, user, and password are required keys.");

        return new self(
            host:               (string) $data['host'],
            user:               (string) $data['user'],
            password:           (string) $data['password'],
            port:               array_key_exists('port', $data) ? (int) $data['port'] : 8076,
            ignoreUnauthorized: array_key_exists('ignoreUnauthorized', $data) ? (bool) $data['ignoreUnauthorized'] : false,
            ca:                 array_key_exists('ca', $data) ? (string) $data['ca'] : null,
        );
    }

    /**
     * Create a new DaemonServer object from an INI file
     * @param string      $path - path to the INI File
     * @param string|null $section - section of the INI file to use for credentials
     * @return self - A new DaemonServer object
     */
    public static function fromIni(string $path, ?string $section=null): self
    {
        $all = parse_ini_file($path, true, INI_SCANNER_TYPED);

        if ($all === false || $all === [])
            throw new \InvalidArgumentException("INI parse failed or is empty: $path");

        if ($section !== null) {
            if(!isset($all[$section]))
                throw new \InvalidArgumentException("Section '$section' not found in INI file.");
        }
        else
            $section = array_key_first($all);
        $data = $all[$section];
            
        if (!isset($data['SERVER'], $data['USER'], $data['PASSWORD']))
            throw new \InvalidArgumentException("INI must include SERVER, USER, and PASSWORD.");

        $data = array_change_key_case($data, CASE_UPPER);

        $ignoreUnauthorized = filter_var($data['IGNOREUNAUTHORIZED'] ?? null, 
                                         FILTER_VALIDATE_BOOLEAN);
        return new self(
            host:               (string) $data['SERVER'],
            user:               (string) $data['USER'],
            password:           (string) $data['PASSWORD'],
            port:               array_key_exists('PORT', $data) ? (int) $data['PORT'] : 8076,
            ignoreUnauthorized: $ignoreUnauthorized,
            ca:                 array_key_exists('CA', $data) ? (string) $data['CA'] : null,
        );
    }
}