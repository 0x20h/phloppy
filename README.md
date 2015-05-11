# Phloppy
[![Latest Version](https://img.shields.io/github/release/0x20h/phloppy.svg?style=flat-square)](https://github.com/0x20h/phloppy/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/0x20h/phloppy/master.svg?style=flat-square)](https://travis-ci.org/0x20h/phloppy)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/0x20h/phloppy.svg?style=flat-square)](https://scrutinizer-ci.com/g/0x20h/phloppy/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/0x20h/phloppy.svg?style=flat-square)](https://scrutinizer-ci.com/g/0x20h/phloppy)
[![Total Downloads](https://img.shields.io/packagist/dt/0x20h/phloppy.svg?style=flat-square)](https://packagist.org/packages/0x20h/phloppy)

A [Disque](https://github.com/antirez/disque) client for PHP 5.5.

## Installation

```
composer require 0x20h/phloppy@~0.1
```

## Usage

### Producer

``` php
$logger = new Monolog\Logger(new Monolog\Handler\StreamHandler('php://stdout'));
$pool = new Phloppy\Stream\Pool(['tcp://127.0.0.1:7711', 'tcp://127.0.0.1:7712']);
$producer = new Phloppy\Producer($pool, $logger);
$job = $producer->addJob('test', Phloppy\Job::create(['body' => 42]));
```

Commands:

- getJob(queues)
- getJobs(queues, numberOfJobs)
- ack(job)
- fastAck(job)


### Consumer

``` php
$logger = new Monolog\Logger(new Monolog\Handler\StreamHandler('php://stdout'));
$pool = new Phloppy\Stream\Pool(['tcp://127.0.0.1:7711', 'tcp://127.0.0.1:7712']);
$consumer = new Phloppy\Consumer($pool, $logger);
$job = $consumer->getJob('test');
// do some work
$consumer->ack($job);
```

Commands:

- getJob(queues)
- getJobs(queues, numberOfJobs)
- ack(job)
- fastAck(job)


### Generic commands

``` php
$logger = new Monolog\Logger(new Monolog\Handler\StreamHandler('php://stdout'));
$pool = new Phloppy\Stream\Pool(['tcp://127.0.0.1:7711', 'tcp://127.0.0.1:7712']);
$consumer = new Phloppy\Client($pool, $logger);
$nodes = $consumer->hello();
```

# License

The MIT License (MIT).
