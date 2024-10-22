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
        $this->client = new Client($this->genURI($server->getHost(), $server->getPort()));
        $this->client->addHeader("Authorization", "Basic " . self::credentialEncoder($server->getUser(), $server->getPassword()));
        $this->client->addMiddleware(new CloseHandler());
        $this->client->addMiddleware(new PingResponder());
//        $this->websocket_client->setTimeout = $this->timeout;
//        $this->websocket_client->setFrameSize = $this->framesize;
//        $this->websocket_client->setPersistent = $this->persistent;
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
     * Formulate the URI for the connection
     * @return string the uri
     */
    private function genURI(string $host, int $port): Uri
    {
        $uri_string = "wss://$host:$port/db/";
        return new Uri($uri_string);
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
