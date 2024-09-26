<?php

use PHPUnit\Framework\TestCase;
use Mapepire\DaemonServer;

class DaemonServerTest extends TestCase
{

    protected function setup(): void
    {
        $this->DaemonServer = DaemonServer::DaemonServerFromDotEnv(dir: 'tests/unit');
    }

    public function testToString(): void
    {
        $this->expectOutputString(
            expectedString:
            "Mapepire\DaemonServer
host: localhost
port: 8076
user: meMyself
password: (hidden)
ignoreUnauthorized: 1
"
        );
        print $this->DaemonServer;
    }

    protected function tearDown(): void
    {
        unset($this->DaemonServer);
    }
    private DaemonServer $DaemonServer;
}