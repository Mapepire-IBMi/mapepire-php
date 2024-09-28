<?php

use PHPUnit\Framework\TestCase;
use Mapepire\SQLJob;
use Mapepire\DaemonServer;

class SQLJobTest extends TestCase
{

    protected function setup(): void
    {
        $this->sqlJob = SQLJob::SQLJobFromEnv(dir: 'tests/unit');
        $this->ds = DaemonServer::DaemonServerFromDotEnv(dir: 'tests/unit');
        $this->sqlJobDs = SQLJob::SQLJobFromDaemonServer(daemonServer: $this->ds);
    }

    public function testToString(): void
    {
        $this->expectOutputString(
            expectedString:
            "Mapepire\SQLJob
host: localhost
port: 8076
user: meMyself
ignoreUnauthorized: 1
verifyHostName: 1
timeout: 60
framesize: 4096
persistent: 
Websocket\Client: WebSocket\Client(closed)
"
        );
        print $this->sqlJob;
    }

    public function testToStringDs(): void
    {
        $this->expectOutputString(
            expectedString:
            "Mapepire\SQLJob
host: localhost
port: 8076
user: meMyself
ignoreUnauthorized: 1
verifyHostName: 1
timeout: 60
framesize: 4096
persistent: 
Websocket\Client: WebSocket\Client(closed)
"
        );
        print $this->sqlJobDs;
    }

    protected function tearDown(): void
    {
        unset($this->sqlJob);
        unset($this->ds);
        unset($this->sqlJobDs);
    }
    private SQLJob $sqlJob;
    private SQLJob $sqlJobDs;
    private DaemonServer $ds;
}