<?php

use PHPUnit\Framework\TestCase;
use Mapepire\Client;

class ClientTest extends TestCase
{

    protected function setup(): void
    {
        $this->client = Client::ClientFromEnv('tests/unit');
    }

    public function testToString()
    {
        $this->expectOutputString("\Mapepire\Client
Server: localhost
Port: 8076
User: X
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