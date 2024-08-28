<?php

declare(strict_types=1);

/**
 * SqlJob.php
 *
 * @author: Yeshua Hall <yeshua@sobo.red>
 * @date: 8/16/24
 */

namespace Mapepire;

use Amp\Socket\ClientTlsContext;
use Amp\Socket\ConnectContext;
use Amp\Websocket\Client\WebsocketHandshake;
use Psr\Log\AbstractLogger;
use React\Socket\ConnectionInterface;
use function Amp\Websocket\Client\connect;

class SqlJob
{
    public function connect(DaemonServer $db2Server): void
    {
        echo "Connecting...\n";
        $encodedAuth = base64_encode("{$db2Server->getUsername()}:{$db2Server->getPassword()}");
        $encodedAuth = 'bWFwZXBpcmU6YnVzaG1hc3RlcjMwMDA';
        echo "encodedAuth: {$encodedAuth}\n";

//        $connector = new \React\Socket\SecureConnector(new \React\Socket\Connector());
//        $connector->connect("{$db2Server->getHost()}:{$db2Server->getPort()}")->then(
//            function (ConnectionInterface $connection) use ($encodedAuth, $db2Server) {
//                echo "successful\n";
//                $data = '{"id":"markwashere", "type":"connect", "technique":"tcp"}';
//                $data = '{"id": "test", "type": "sql", "rows":4, "sql":"select * from qsys2.columns"}';
//                $head = "GET /db/ HTTP/1.1\r\n".
//                    "Upgrade: websocket\r\n" .
//                    "Connection: Upgrade\r\n" .
//                    "Sec-WebSocket-Version: 13\r\n" .
//                    "Sec-WebSocket-Key: ".base64_encode('testkey')."\r\n" .
//                    "Authorization: Basic $encodedAuth\r\n" .
//                    "Origin: http://idevphp.idevcloud.com/\r\n" .
//                    "Host: {$db2Server->getHost()}\r\n" .
//                    "Content-Length: ".strlen($data)."\r\n\r\n";
//                $connection->pipe(new \React\Stream\WritableResourceStream(STDOUT));
//                $connection->write($head);
//                $connection->write("$data\r\n");
//            },
//            function (\Exception $exception) {
//                echo "failure\n";
//                echo $exception->getMessage() . "\n";
//            }
//        );

//        try {
//            $randStr = 'Hello world!';
//            $client = new \WebSocket\Client("wss://{$db2Server->getHost()}:{$db2Server->getPort()}/db/");
//            $client->onHandshake(function ($server, $connection, $request, $response) {
//                echo "> [{$connection->getRemoteName()}] Server connected {$response->getStatusCode()}\n";
//            })->onDisconnect(function ($client, $connection) {
//                echo "> [{$connection->getRemoteName()}] Server disconnected\n";
//            });
//            $client->connect();
//        } catch (\Throwable $e) {
//            echo "> ERROR: {$e->getMessage()}\n";
//        }
//        $client->close();


//        $clientTlsContext = (new ClientTlsContext(''))
//            ->withoutPeerVerification()
//            ->withSecurityLevel(0);
//        $connectContext = (new ConnectContext)
//            ->withTlsContext($clientTlsContext);
//        $socket = connect("{$db2Server->getHost()}:{$db2Server->getPort()}/db/", $connectContext);
//        $socket->write('{"id": "1l", "type": "sql", "rows":4, "sql":"select * from qiws.qcustcddt"}');
//        $socket->read();
//        $socket->close();
//        $handshake = (new WebsocketHandshake("wss://{$db2Server->getHost()}:{$db2Server->getPort()}/db/"))
//            ->withHeader('Authorization', "Basic {$encodedAuth}");
//        $connection = connect($handshake);
//        var_dump($connection);
//
//        $host = $db2Server->getHost();
//        $origin = "http://$host/";
//
//        $head = "GET / HTTP/1.1\r\n" .
//            "Upgrade: WebSocket\r\n" .
//            "Connection: Upgrade\r\n" .
//            "Origin: $$origin\r\n" .
//            "Host: $host\r\n" .
//            "Authorization: Basic {$encodedAuth}\r\n" .
//            "Content-Length: 0\r\n" .
//            "Accept-Encoding: gzip, deflate\r\n" .
//            "Accept-Language: de-DE,de;q=0.9,en-US;q=0.8,en;q=0.7\r\n"
//            ."\r\n";
//        $socket = fsockopen($db2Server->getHost(), $db2Server->getPort(), $errno, $errstr, 30);
//        fwrite($socket, $head) or die("error: $errno - $errstr\n");
//        $headers = fread($socket, 2000);
//        var_dump($headers);
//        fwrite($socket, '{"id": "1l", "type": "sql", "rows":4, "sql":"select * from qiws.qcustcddt"}') or die("error: $errno - $errstr\n");
//        $res = fread($socket, 2000);
//        var_dump($res);

//        $handshake = (new WebsocketHandshake('wss://libwebsockets.org'))
//            ->withHeader('Sec-WebSocket-Protocol', 'dumb-increment-protocol');

        $data = '{"id": "test", "type": "sql", "rows":4, "sql":"select * from qsys2.columns"}';
        $dataLength = strlen($data);
        $handshake = (new WebsocketHandshake("wss://{$db2Server->getHost()}:{$db2Server->getPort()}/db/"))
            ->withHeader('Upgrade', 'websocket')
            ->withHeader('Connection', 'Upgrade')
            ->withHeader('Sec-Websocket-Version', '13')
            ->withHeader('Sec-WebSocket-Key', base64_encode('testkey'))
            ->withHeader('Authorization', "Basic {$encodedAuth}")
            ->withHeader('Content-Length', "$dataLength");

        $connection = connect($handshake);
//        try {
//            $connection = connect($handshake);
//        } catch (\Exception $e) {
//            echo $e->getMessage();
//            die();
//        }

        foreach ($connection as $message) {
            $payload = $message->buffer();

            printf("Received: %s\n", $payload);

            if ($payload === '100') {
                $connection->close();
                break;
            }
        }
    }
}
