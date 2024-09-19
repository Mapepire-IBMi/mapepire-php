<?php

use PHPUnit\Framework\TestCase;
use Mapepire\Client;

class ClientTest extends TestCase
{

    protected function setup(): void
    {
        $this->client = Client::ClientFromEnv(dir: 'tests/unit');
    }

    public function testToString(): void
    {
        $this->expectOutputString(
            expectedString:
            "\Mapepire\Client
Server: localhost
Port: 8076
User: X
Websocket Client: WebSocket\Client(closed)
"
        );
        print $this->client;
    }

    protected function tearDown(): void
    {
        unset($this->client);
    }
    private Client $client;
}