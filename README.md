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

### Connecting with an associative array
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

A Query object can be used to build a query and send it to a server. This is done through SQLJob's `query()` method:
```
require_once('vendor/autoload.php');

use Mapepire\SQLJob;
use Mapepire\Query;

$sqlJob = new SQLJob("/path/to/file/Mapepire.ini");

$query = $sqljob->query("select * from sample.employee");
$result = $query->run(rows: 1);

print_r($result);
```

Alternatively, SQLJob's `queryAndRun()` method can be used:
```
require_once('vendor/autoload.php');

use Mapepire\SQLJob;

$sqlJob = new SQLJob("/path/to/file/Mapepire.ini");

$result = $sqljob->queryAndRun("select * from sample.employee", rows: 1);

print_r($result);
```

Both implementations above code will result in the following output:
```
Array
(
    [id] => query3
    [has_results] => 1
    [update_count] => -1
    [metadata] => Array
        (
            [column_count] => 14
            [job] => 325440/QUSER/QZDASOINIT
            [columns] => Array
                (
                    [0] => Array
                        (
                            [name] => EMPNO
                            [type] => CHAR
                            [display_size] => 6
                            [label] => EMPNO
                            [precision] => 6
                            [scale] => 0
                        )

                    [1] => Array
                        (
                            [name] => FIRSTNME
                            [type] => VARCHAR
                            [display_size] => 12
                            [label] => FIRSTNME
                            [precision] => 12
                            [scale] => 0
                        )

                    [2] => Array
                        (
                            [name] => MIDINIT
                            [type] => CHAR
                            [display_size] => 1
                            [label] => MIDINIT
                            [precision] => 1
                            [scale] => 0
                        )

                    [3] => Array
                        (
                            [name] => LASTNAME
                            [type] => VARCHAR
                            [display_size] => 15
                            [label] => LASTNAME
                            [precision] => 15
                            [scale] => 0
                        )

                    [4] => Array
                        (
                            [name] => WORKDEPT
                            [type] => CHAR
                            [display_size] => 3
                            [label] => WORKDEPT
                            [precision] => 3
                            [scale] => 0
                        )

                    [5] => Array
                        (
                            [name] => PHONENO
                            [type] => CHAR
                            [display_size] => 4
                            [label] => PHONENO
                            [precision] => 4
                            [scale] => 0
                        )

                    [6] => Array
                        (
                            [name] => HIREDATE
                            [type] => DATE
                            [display_size] => 10
                            [label] => HIREDATE
                            [precision] => 10
                            [scale] => 0
                        )

                    [7] => Array
                        (
                            [name] => JOB
                            [type] => CHAR
                            [display_size] => 8
                            [label] => JOB
                            [precision] => 8
                            [scale] => 0
                        )

                    [8] => Array
                        (
                            [name] => EDLEVEL
                            [type] => SMALLINT
                            [display_size] => 6
                            [label] => EDLEVEL
                            [precision] => 5
                            [scale] => 0
                        )

                    [9] => Array
                        (
                            [name] => SEX
                            [type] => CHAR
                            [display_size] => 1
                            [label] => SEX
                            [precision] => 1
                            [scale] => 0
                        )

                    [10] => Array
                        (
                            [name] => BIRTHDATE
                            [type] => DATE
                            [display_size] => 10
                            [label] => BIRTHDATE
                            [precision] => 10
                            [scale] => 0
                        )

                    [11] => Array
                        (
                            [name] => SALARY
                            [type] => DECIMAL
                            [display_size] => 11
                            [label] => SALARY
                            [precision] => 9
                            [scale] => 2
                        )

                    [12] => Array
                        (
                            [name] => BONUS
                            [type] => DECIMAL
                            [display_size] => 11
                            [label] => BONUS
                            [precision] => 9
                            [scale] => 2
                        )

                    [13] => Array
                        (
                            [name] => COMM
                            [type] => DECIMAL
                            [display_size] => 11
                            [label] => COMM
                            [precision] => 9
                            [scale] => 2
                        )

                )

        )

    [data] => Array
        (
            [0] => Array
                (
                    [EMPNO] => 000010
                    [FIRSTNME] => CHRISTINE
                    [MIDINIT] => I
                    [LASTNAME] => HAAS
                    [WORKDEPT] => A00
                    [PHONENO] => 3978
                    [HIREDATE] => 01/01/65
                    [JOB] => PRES
                    [EDLEVEL] => 18
                    [SEX] => F
                    [BIRTHDATE] => 
                    [SALARY] => 52750
                    [BONUS] => 1000
                    [COMM] => 4220
                )

        )

    [is_done] => 
    [success] => 1
    [execution_time] => 185
)
```

The result is an associate array containing the metadata and data from the query. Here are the different fields returned:

    * id field contains the query ID
    * has_results field indicates whether the query returned any results
    * update_count field indicates the number of rows updated by the query (-1 if the query did not update any rows)
    * metadata field contains information about the columns returned by the query
    * data field contains the results of the query
    * is_done field indicates whether the query has finished executing
    * success field indicates whether the query was successful.
    * execution_time field indicates the time it took to execute the query

In the ouput above, the query was successful and returned one row of data.

## Documentation

PHP Composer command `composer doc` will use phpDocumentor to generate the API documentation.

## Tests

PHP Composer command `composer test` will run the test cases. Currently, the test cases are limited to instancing the classes. (WIP)

## Asynchronous support

This will be coming.
