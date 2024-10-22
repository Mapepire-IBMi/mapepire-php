<?php

/*
 * Copyright 2024 IBM
 * All Rights Reserved
 * Apache License 2.0 ... see LICENSE file
 * Author: Jack J. Woehr
 * jwoehr@softwoehr.com
 */

namespace Mapepire;

use Phrity\Net\Uri;
use WebSocket\Client;
use WebSocket\Message\Message;
use WebSocket\Middleware\CloseHandler;
use WebSocket\Middleware\PingResponder;

/**
 * SQLJob is a client to the Mapepire Server
 * @see https://mapepire-ibmi.github.io/
 * @see https://github.com/Mapepire-IBMi/mapepire-server
 */
class SQLJob
{
    private Client $client;
    private DaemonServer $server;

    public function connect(DaemonServer $server): void
    {
        $this->server = $server;
        $this->client = new Client(new Uri("wss://{$server->getHost()}:{$server->getPort()}/db/"));
        $this->client->addHeader("Authorization", "Basic " . self::credentialEncoder($server->getUser(), $server->getPassword()));
        $this->client->addMiddleware(new CloseHandler());
        $this->client->addMiddleware(new PingResponder());
        $this->client->setTimeout($server->getTimeout());
        $this->client->setFrameSize($server->getFrameSize());
        $this->client->setPersistent($server->getPersistent());
    }

    /**
     * Send message and receive response.
     * Connects if not currently connected.
     * @param string $message the JSON message to the Mapepire server
     * @param mixed $sslContext the specific SSL Context. If none, uses the context created by ctor.
     * @return \WebSocket\Message\Text the response text object .. ->getContent() to get the JSON message content
     */
    public function singleSendAndReceive(string $message, ?array $sslContext = null): Message
    {
        $sslContext = $sslContext ?: [
            "verify_peer" => $this->server->getVerifyHostCert(),
            "verify_peer_name" => $this->server->getVerifyHostName(),
        ];
        $this->client->setContext(["ssl" => $sslContext]);
        $this->client->text($message);
        return $this->client->receive();
    }

    /**
     * Close the websocket connection
     * @return void
     */
    public function close(): void
    {
        $this->client->close();
    }

    /**
     * base64 encode credentials for Basic Auth
     * @param string $user the user
     * @param string $password the password
     * @return string the encoded creds
     */
    public static function credentialEncoder(string $user, string $password): string
    {
        return base64_encode("$user:$password");
    }
}
