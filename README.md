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
that holds the link to the connected node.

The first thing to do is to connect to a Disque node. For that, use one of the
`StreamInterface` implementations.

``` php
$cache  = new FileCache('/tmp/nodes');
$stream = new CachedPool(['tcp://127.0.0.1:7711', 'tcp://127.0.0.1:7712'], $cache);
$stream->connect();
```

Then, inject the `$stream` into a client, i.e. a `Consumer`.

``` php
$consumer = new Consumer($stream);
while (true) {
   $job = $consumer->getJob('my_queue');
   // process $job
}
```

## Clients

Clients are separated into `Producer`, `Consumer`, `Node`, `Queue` and `Cluster`.
Every client contains methods related to their specific use-case.

### Producer

Holds all API commands related to submitting jobs to a Disque cluster.

``` php
$producer = new Producer($stream);
$job = $producer->addJob('test', Job::create(['body' => 42]));
```

Commands:

- `addJob(queueName, job, [maxlen = 0], [async = false])`
- `setReplicationTimeout(msecs)`
- `setReplicationFactor(n)`

### Consumer

Implements all commands related to getting jobs from a Disque cluster.

``` php
$consumer = new Consumer($stream);
$job = $consumer->getJob('test');
// do some work
$consumer->ack($job);
```

Commands:

- `getJob(queueNames)`
- `getJobs(queueNames, numberOfJobs)`
- `ack(job)`
- `fastAck(job)`
- `show(jobid)`

### Queue

``` php
$queue = new Queue($stream);
// print out the current queue len on the connected node
echo $queue->len('test');
// get the latest job out of 'test' without removing it
echo $queue->peek('test');
```

Commands:

- `len(queueName)`
- `peek(queueName)`
- `scan(count,min,max,rate)`
- `enqueue(jobIds)`
- `dequeue(jobIds)`

### Node

Contains commands related to a single Disque instance.

``` php
$consumer = new Node($stream);
$nodes = $consumer->hello();
```

Commands:

- `hello()`
- `info()`
- `ping()`
- `auth(password)`
- `jscan(count, queues[], states[], format)`

### Cluster

``` php
$cluster = new Cluster($stream);
$cluster->meet($stream->getStreamUrls());
```

Commands:

- `meet($urls)`

## Streams

### DefaultStream

Connect to a single node. If the connection fails, a `ConnectException` thrown.
If the node fails, a StreamException is thrown.

``` php
$stream = new DefaultStream('tcp://127.0.0.1:7711');
```

### Pool

Connect randomly to on of the provided nodes. If during operation one of the nodes dies or doesn't respond anymore
the `Pool` automatically reconnects to one of the other nodes. If no other node is left, a `ConnectException` is thrown.

``` php
$stream = new Pool(['tcp://127.0.0.1:7711', 'tcp://127.0.0.1:7712']);
```

### CachedPool

Same behavior as the `Pool` implementation, but you can provide a `CacheInterface` implemention
in order to cache all existing cluster nodes. When connecting, a random node from the cached 
cluster nodes is chosen.

``` php
$cache = new FileCache('/tmp/nodes');
$stream = new CachedPool(['tcp://127.0.0.1:7711'], $cache);
```


# License

The MIT License (MIT).
