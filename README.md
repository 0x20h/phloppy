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
composer require 0x20h/phloppy:~0.0
```

## Usage

Disque's API is implemented in different `\Phloppy\Client` implementations that
reflect their specific use case. All clients get injected a `StreamInterface`
that holds the link to the connected Disque node. The different Clients are:

### Setup a stream

First thing to do is to connect to a Disque server. You can use the
`Phloppy\Stream\Pool` class to connect to a random instance in the cluster.

``` php
$pool = new Pool(['tcp://127.0.0.1:7711', 'tcp://127.0.0.1:7712']);
```

Then, inject the `$pool` to a client implementation.

### Producer

``` php
$producer = new Producer($pool);
$job = $producer->addJob('test', Phloppy\Job::create(['body' => 42]));
```

Further Commands:

- `addJob(queueName, job, [maxlen = 0], [async = false])`
- `setReplicationTimeout(msecs)`
- `setReplicationFactor(n)`

### Consumer

``` php
$consumer = new Consumer($pool);
$job = $consumer->getJob('test');
// do some work
$consumer->ack($job);
```

Further commands:

- `getJob(queueNames)`
- `getJobs(queueNames, numberOfJobs)`
- `ack(job)`
- `fastAck(job)`
- `findJob(jobid)`

### Queue

``` php
$queue = new Queue($pool);
// print out the current queue len on the connected node
echo $queue->len('test');
// get the latest job out of 'test' without removing it
echo $queue->peek('test');
```

Commands:

- `len(queueName)`
- `peek(queueName)`
- `scan(count,min,max,rate)`

### Server

``` php
$consumer = new Server($pool);
$nodes = $consumer->hello();
```

Commands:

- `hello()`
- `info()`
- `ping()`
- `auth(password)`

### Cluster

``` php
$cluster = new Cluster($pool);
$cluster->meet($pool->getStreamUrls());
```

Commands:

- `meet($urls)`

# License

The MIT License (MIT).
