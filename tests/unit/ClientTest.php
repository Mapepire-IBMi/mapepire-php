<?php
use PHPUnit\Framework\TestCase;
use Mapepire\Client;

class ClientTest extends TestCase
{

    protected function setup(): void
    {
        $this->client = new Client();
    }

    public function testToString()
    {
        $this->expectOutputString("\Mapepire\Client");
        print $this->client;
    }

    protected function tearDown(): void
    {
        unset($this->client);
    }
    private Client $client;
}