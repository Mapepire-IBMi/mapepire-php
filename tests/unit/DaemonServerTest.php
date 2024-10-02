<?php

use PHPUnit\Framework\TestCase;
use Mapepire\DaemonServer;

class DaemonServerTest extends TestCase
{

    protected function setup(): void
    {
        $this->daemonServer = DaemonServer::DaemonServerFromDotEnv(dir: 'tests/unit');
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
verifyHostCert: 1
verifyHostName: 1
timeout: 60
framesize: 4096
persistent: 
"
        );
        print $this->daemonServer;
    }

    protected function tearDown(): void
    {
        unset($this->daemonServer);
    }
    private DaemonServer $daemonServer;
}