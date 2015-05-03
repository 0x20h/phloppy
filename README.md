# Disque-php

A [Disque](https://github.com/antirez/disque) client for PHP 5.5.

## Usage

Producer:
``` php
$logger = new Monolog\Logger(new Monolog\Handler\StreamHandler('php://stdout'));
$pool = new Disque\Stream\Pool(['tcp://127.0.0.1:7711', 'tcp://127.0.0.1:7712']);
$producer = new Disque\Producer($pool, $logger);
$job = $producer->addJob('test', Disque\Job::create(['body' => 42]));
```

Consumer:
``` php
$logger = new Monolog\Logger(new Monolog\Handler\StreamHandler('php://stdout'));
$pool = new Disque\Stream\Pool(['tcp://127.0.0.1:7711', 'tcp://127.0.0.1:7712']);
$consumer = new Disque\Consumer($pool, $logger);
$job = $consumer->getJob('test');
// do some work
$consumer->ack($job);
```
