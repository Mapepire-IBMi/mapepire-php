<?php

declare(strict_types=1);

/**
 * DaemonServer.php
 *
 * @author: Yeshua Hall <yeshua@sobo.red>
 * @date: 8/16/24
 */

namespace Mapepire;

class DaemonServer
{
    private string $host;
    private int $port;
    private string $username;
    private string $password;
    private bool $ignoreUnauthorized;
    private string $ca;

    public function __construct(
        string $username = '',
        string $password = '',
        string $host = 'localhost',
        int $port = 8076,
        bool $ignoreUnauthorized = false,
        string $ca = ''
    ) {
        $this->setHost($host);
        $this->setPort($port);
        $this->setUsername($username);
        $this->setPassword($password);
        $this->setIgnoreUnauthorized($ignoreUnauthorized);
        $this->setCa($ca);
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function setPort(int $port): void
    {
        $this->port = $port;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function isIgnoreUnauthorized(): bool
    {
        return $this->ignoreUnauthorized;
    }

    public function setIgnoreUnauthorized(bool $ignoreUnauthorized): void
    {
        $this->ignoreUnauthorized = $ignoreUnauthorized;
    }

    public function getCa(): string
    {
        return $this->ca;
    }

    public function setCa(string $ca): void
    {
        $this->ca = $ca;
    }


}
