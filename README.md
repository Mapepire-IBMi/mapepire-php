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

## Usage

### Usage synopsis

The simplest use is to instance an `SQLJob` and use its instance method `singleSendAndReceive()` to send JSON and receive a JSON response.

The response comes in the form of a [`phrity/websocket`](https://github.com/sirn-se/websocket-php) `\WebSocket\Message\Text` object.

Use the `\WebSocket\Message\Text` instance method `getContent()` to get the JSON message content.

You may further more use any other `\WebSocket\Message\Text` instance method on the response to examine the response and help debug your code.

### Instancing `SQLJob`

There are 3 ways to instance `SQLJob`:

1. The `SQLJob` constructor with its numerous arguments, all of which default to "reasonable" values.
    - Of course, `userid` and `password` cannot default.
1. `SQLJob::SQLJobFromDotEnv()` which uses [`vlucas/phpdotenv`](https://github.com/vlucas/phpdotenv) to load a .env file in a specified directory.
    - See the `.env.sample` file is the root directory of the project.
1. `SQLJob::SQLJobFromDaemonServer()` which takes an instance of `DaemonServer`.

### Instancing `DaemonServer`

There are 2 ways to instance `DaemonServer`.

1. The `DaemonServer` constructor with its numerous arguments, all of which default to "reasonable" values.
1. `DaemonServer::DaemonServerFromDotEnv()` which uses [`vlucas/phpdotenv`](https://github.com/vlucas/phpdotenv) to load a .env file in a specified directory.
    - See the `.env.sample` file is the root directory of the project.

### Example session

Assuming a reasonble `.env` file in the current directory, in interpretive PHP (`php -a`):

```php
php > require_once('vendor/autoload.php');
php > $ds = Mapepire\DaemonServer::DaemonServerFromDotEnv('.');
php > $sj = Mapepire\SQLJob::SQLJobFromDaemonServer($ds);
php > $result = $sj->singleSendAndReceive('{"id": "foo", "type": "connect"}');
php > print($result->getContent());
{"id":"foo","job":"242155/QUSER/QSQSRVR","success":true}
php > $result = $sj->singleSendAndReceive('{"id": "bar", "type": "sql", "sql": "select * from sample.employee"}');
php > print($result->getContent());
{"id":"bar","has_results":true,"update_count":-1,"metadata":{"column_count":14,"job":"242155/QUSER/QSQSRVR","columns":[{"name":"EMPNO","type":"CHAR","display_size":6,"label":"EMPNO"},{"name":"FIRST_NAME","type":"VARCHAR","display_size":12,"label":"FIRST_NAME"},{"name":"MIDINIT","type":"CHAR","display_size":1,"label":"MIDINIT"},{"name":"LASTNAME","type":"VARCHAR","display_size":15,"label":"LASTNAME"},{"name":"WORKDEPT","type":"CHAR","display_size":3,"label":"WORKDEPT"},{"name":"PHONENO","type":"CHAR","display_size":4,"label":"PHONENO"},{"name":"HIREDATE","type":"DATE","display_size":10,"label":"HIREDATE"},{"name":"JOB","type":"CHAR","display_size":8,"label":"JOB"},{"name":"EDLEVEL","type":"SMALLINT","display_size":6,"label":"EDLEVEL"},{"name":"SEX","type":"CHAR","display_size":1,"label":"SEX"},{"name":"BIRTHDATE","type":"DATE","display_size":10,"label":"BIRTHDATE"},{"name":"SALARY","type":"DECIMAL","display_size":11,"label":"SALARY"},{"name":"BONUS","type":"DECIMAL","display_size":11,"label":"BONUS"},{"name":"COMM","type":"DECIMAL","display_size":11,"label":"COMM"}]},"data":[{"EMPNO":"000010","FIRST_NAME":"CHRISTINE","MIDINIT":"I","LASTNAME":"HAAS","WORKDEPT":"A00","PHONENO":"3978","HIREDATE":"1965-01-01","JOB":"PRES","EDLEVEL":18,"SEX":"F","BIRTHDATE":"1933-08-24","SALARY":52750.00,"BONUS":1000.00,"COMM":4220.00},{"EMPNO":"000020","FIRST_NAME":"MICHAEL","MIDINIT":"L","LASTNAME":"THOMPSON","WORKDEPT":"B01","PHONENO":"3476","HIREDATE":"1973-10-10","JOB":"MANAGER","EDLEVEL":18,"SEX":"M","BIRTHDATE":"1948-02-02","SALARY":41250.00,"BONUS":800.00,"COMM":3300.00},
```

... etc.

## Documentation

PHP Composer command `composer doc` will use phpDocumentor to generate the API documentation.

## Tests

PHP Composer command `composer test` will run the test cases. Currently, the test cases are limited to instancing the classes. (WIP)

## Asynchronous support

This will be coming.
