<?php

namespace Disque;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class ProducerIntegrationTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var \Disque\Stream\Pool
     */
    private $link;

    protected function setUp()
    {
        if (empty($_ENV['DISQUE_SERVERS'])) {
            return $this->markTestSkipped('no disque servers configured');
        }

        $this->link = $this->getPool();
    }

    protected function tearDown()
    {
        $this->link->close();
        $this->link = null;
    }


    public function testAddJob()
    {
        $queue = 'test-'.substr(sha1(mt_rand()), 6);
        $client= new Producer($this->link);
        $job = new Job("foo");
        $this->assertNull($job->getId());
        $client->addJob($queue, $job);
        $this->assertNotNull($job->getId());
    }


    public function testAddJobDelayed()
    {
        $queue = 'test-'.substr(sha1(mt_rand()), 6);
        $producer = new Producer($this->link);
        $consumer = new Consumer($this->link);
        $job = new Job("foo");
        $job->setDelay(1);
        $job->setTtl(4);
        $job->setRetry(1);
        $this->assertNull($job->getId());
        $producer->addJob($queue, $job);
        $this->assertNotNull($job->getId());
        $this->assertNull($consumer->getJob($queue));
        sleep(1);
        $j = $consumer->getJob($queue);
        $this->assertInstanceOf('\Disque\Job', $j);
    }

    private function getPool()
    {
        $servers = explode(',', $_ENV['DISQUE_SERVERS']);
        return new Stream\Pool($servers);
    }
}
