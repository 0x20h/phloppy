<?php

namespace Disque;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class ConsumerIntegrationTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var Pool
     */
    private $link;

    protected function setUp()
    {
        $servers = explode(',', $_ENV['DISQUE_SERVERS']);
        $this->link = new Stream\Pool($servers);
    }


    public function testGetJob()
    {
        $queue = 'test-'.substr(sha1(mt_rand()), 6);
        $client= new Consumer($this->link);
        $job = $client->getJob($queue, 1);
        $this->assertNull($job);
    }

    public function testAckUnknownJob()
    {
        $queue = 'test-'.substr(sha1(mt_rand()), 6);
        $client= new Consumer($this->link);
        // ack an unknown job
        $job = Job::create(['id' => 'DI37a52bb8dc160e3953111b6a9a7b10f56209320d0002SQ', 'body' => 'foo']);
        $this->assertEquals(0, $client->ack($job));
    }


    public function testAckNewJob()
    {
        $queue = 'test-'.substr(sha1(mt_rand()), 6);

        $consumer= new Consumer($this->link);
        $producer= new Producer($this->link);
        $job = $producer->addJob($queue, Job::create(['body' => '42']));
        $consumer->getJob($queue);
        $this->assertEquals(1, $consumer->ack($job));
        $this->assertEquals(0, $consumer->ack($job));
    }


    public function testFastAck()
    {
        $queue = 'test-'.substr(sha1(mt_rand()), 6);
        $consumer= new Consumer($this->link);
        $producer= new Producer($this->link);
        $job = $producer->addJob($queue, Job::create(['body' => '42']));
        $this->assertEquals(1, $consumer->ack($job,1));
        $this->assertEquals(0, $consumer->ack($job));
    }
}
