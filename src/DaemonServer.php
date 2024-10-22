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
class DaemonServer
{
    const DEFAULT_HOST_NAME = 'localhost';
    const DEFAULT_PORT = 8076;
    const DEFAULT_VERIFY_HOST_CERT = true;
    const DEFAULT_VERIFY_HOST_NAME = true;
    const DEFAULT_TIMEOUT = 60;
    const DEFAULT_FRAMESIZE = 4096;
    const DEFAULT_PERSISTENCE = false;

    /**
     * DNS or IP of Mapepire server
     * @var ?string
     */
    private ?string $host;

    /**
     * port number of Mapepire host
     * @var ?int
     */
    private ?int $port;

    /**
     * User profile for IBM i Db2
     * @var ?string
     */
    private ?string $user;

    /**
     * Password for IBM i Db2
     * @var ?string
     */
    private ?string $password;

    /**
     * verifyHostCert .IFF. false accept snakeoil cert
     * @var bool
     */
    private ?bool $verifyHostCert;

    /**
     * verifyHostName .IFF. true
     * @var ?bool
     */
    private ?bool $verifyHostName;

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
     * Summary of __construct
     * @param string $host Mapepire server host
     * @param string $user Userid to authorize on Mapepire server
     * @param string $password Password to authorize on Mapepire server
     * @param int $port Mapepire server port
     * @param bool $verifyHostCert .IFF. false accept self-signed, default true
     * @param bool $verifyHostName .IFF. true verify the host name
     */
    public function __construct(
        string $host = self::DEFAULT_HOST_NAME,
        string $user = null,
        string $password = null,
        int $port = self::DEFAULT_PORT,
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

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function setHost(?string $host): void
    {
        $this->host = $host;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function setPort(?int $port): void
    {
        $this->port = $port;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function setUser(?string $user): void
    {
        $this->user = $user;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    public function getVerifyHostCert(): ?bool
    {
        return $this->verifyHostCert;
    }

    public function setVerifyHostCert(?bool $verifyHostCert): void
    {
        $this->verifyHostCert = $verifyHostCert;
    }

    public function getVerifyHostName(): ?bool
    {
        return $this->verifyHostName;
    }

    public function setVerifyHostName(?bool $verifyHostName): void
    {
        $this->verifyHostName = $verifyHostName;
    }

    public function getTimeout(): ?int
    {
        return $this->timeout;
    }

    public function setTimeout(?int $timeout): void
    {
        $this->timeout = $timeout;
    }

    public function getFramesize(): ?int
    {
        return $this->framesize;
    }

    public function setFramesize(?int $framesize): void
    {
        $this->framesize = $framesize;
    }

    public function getPersistent(): ?bool
    {
        return $this->persistent;
    }

    public function setPersistent(?bool $persistent): void
    {
        $this->persistent = $persistent;
    }
}
