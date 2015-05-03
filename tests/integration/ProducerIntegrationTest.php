<?php

namespace Disque;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class ProducerIntegrationTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var Pool
     */
    private $link;

    protected function setUp()
    {
        $servers = explode(',', $_ENV['DISQUE_SERVERS']);
        $this->link = new Stream\Pool($servers);
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

}
