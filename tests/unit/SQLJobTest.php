<?php

use PHPUnit\Framework\TestCase;
use Mapepire\SQLJob;

class SQLJobTest extends TestCase
{

    protected function setup(): void
    {
        $this->SQLJob = SQLJob::SQLJobFromEnv(dir: 'tests/unit');
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
Websocket\Client: WebSocket\Client(closed)
"
        );
        print $this->SQLJob;
    }

    protected function tearDown(): void
    {
        unset($this->SQLJob);
    }
    private SQLJob $SQLJob;
}