# mapepire-php

`mapepire-php` is a PHP client for the [Mapepire](https://github.com/Mapepire-IBMi) database access layer for Db2 on IBM i.

## ⚠️ (WIP!) This Project is a work in progress, more features are coming

## Summary

`mapepire-php` currently consists of two classes:

- `DaemonServer`
  - a structure for initializing an SQLJob
- `SQLJob`
  - the basic client which can connect, send a JSON request to the `mapepire-server`, and receive a response

## Installation

### From the repository

- Clone the repository and use PHP Composer to do `composer install` to download the component packages.
- In your code `require_once('vendor/autoload.php');` will pull in the component packages along with `mapepire-php` classes.

### From Composer

- To be implemented

# Usage

## Connecting to the Server

There are four ways to instance 'SQLJob' and then connect:

1. Passing in an associative array containing authentication information
2. Passing in a `DaemonServer` object containing the authenication information
3. Passining in the path to a `.ini` file listing the authenication information
4. Create an empty instance and call `SQLJob::connect()` with one of the above passed in

### Connecting with an associate array
The simplest and quickest way to connect to a server is to instance an `SQLJob` with a associative array as input:
_Note: it is not recommended to set 'ignoreUnauthorized' to true in a live environment. See section on authentication information._

```
require_once('vendor/autoload.php');

use Mapepire\SQLJob;

$creds = [
  'host' => "SERVER",
  'user' => "USER",
  'password' => "PASSWORD",
  'ignoreUnauthorized' => true,
];

$connection = new SQLJob($creds);
```

### Connecting with a DaemonServer object
A `DaemonServer` object can also be created in a similar fashion and passed in:

```
require_once('vendor/autoload.php');

use Mapepire\SQLJob;
use Mapepire\DaemonServer;

$ds = new DaemonServer(
    host: "SERVER",
    user: "USER",
    password: "PASSWORD",
    ignoreUnauthorized: true
);

$connection = newSQLJob($ds);
```

### Connecting with a `.ini` file
A path to a `.ini` file can be passed into `SQLJob` while creating an instance:

Mapepire.ini:
```
[mapepire]
SERVER="SERVER"
USER="USER"
PASSWORD="PASSWORD"
IGNOREUNAUTHORIZED="TRUE"
```

PHP:
```
require_once('vendor/autoload.php');

use Mapepire\SQLJob;

$connection = new SQLJob("/path/to/file/Mapepire.ini");
```

Optionally, you may specify a particular section of a `.ini` file by passing a second argument:

PHP:
```
require_once('vendor/autoload.php');

use Mapepire\SQLJob;

$connection = new SQLJob("/path/to/file/Mapepire.ini", "mapepire");
```

### Connecting with `.connect()`

A connection can also be made after instancing `SQLJob`, for example with a DaemonServer object:

```
require_once('vendor/autoload.php');

use Mapepire\SQLJob;
use Mapepire\DaemonServer;

$ds = new DaemonServer(
    host: "SERVER",
    user: "USER",
    password: "PASSWORD",
    ignoreUnauthorized: true
);

$connection = newSQLJob();

$connection->connect($ds);
```

## Authentication Information
Regardless of connection method, the client performs authentication with information stored in a DaemonServer instance. The following may be passed for a connection:

1. host (required) - the host name of the server
2. username (required) - the user profile connecting to the server
3. password (required) - the password of the user profile
4. port (optional) - the server port, defaults to 8076
5. ignoreUnauthorized (optional) - a boolean value determining whether to ignore if a user in unauthorized, defaults to false
6. ca (optional) - the path to a certificate determining that a user is authorized. If (5) is false, this is required. 

## Getting a response

The response comes in the form of a [`phrity/websocket`](https://github.com/sirn-se/websocket-php) `\WebSocket\Message\Text` object.

Use the `\WebSocket\Message\Text` instance method `getContent()` to get the JSON message content.

You may further more use any other `\WebSocket\Message\Text` instance method on the response to examine the response and help debug your code.

## Querying the Server

This will be coming. 

## Documentation

PHP Composer command `composer doc` will use phpDocumentor to generate the API documentation.

## Tests

PHP Composer command `composer test` will run the test cases. Currently, the test cases are limited to instancing the classes. (WIP)

## Asynchronous support

This will be coming.
