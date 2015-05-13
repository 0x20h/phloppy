<?php

namespace Phloppy;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Phloppy\Exception\ConnectException;

class ConsumerIntegrationTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var Stream
     */
    private $stream;

    protected function setUp()
    {
        if (empty($_ENV['DISQUE_SERVERS'])) {
            return $this->markTestSkipped('no disque servers configured');
        }

        try {
            $servers = explode(',', $_ENV['DISQUE_SERVERS']);
            $this->stream = new Stream\Pool($servers);
        } catch (ConnectException $e) {
            $this->markTestSkipped($e->getMessage());
        }
    }


    protected function tearDown()
    {
        if ($this->stream) {
            $this->stream->close();
            $this->stream = null;
        }
    }


    public function testGetJob()
    {
        $queue = 'test-'.substr(sha1(mt_rand()), 6);
        $client= new Consumer($this->stream);
        $job = $client->getJob($queue, 1);
        $this->assertNull($job);
    }

    public function testAckUnknownJob()
    {
        $queue = 'test-'.substr(sha1(mt_rand()), 6);
        $client= new Consumer($this->stream);
        // ack an unknown job
        $job = Job::create(['id' => 'DI37a52bb8dc160e3953111b6a9a7b10f56209320d0002SQ', 'body' => 'foo']);
        $this->assertEquals(0, $client->ack($job));
    }


    public function testAckNewJob()
    {
        $queue = 'test-'.substr(sha1(mt_rand()), 6);

        $consumer= new Consumer($this->stream);
        $producer= new Producer($this->stream);
        $job = $producer->addJob($queue, Job::create(['body' => '42']));
        $consumer->getJob($queue);
        $this->assertEquals(1, $consumer->ack($job));
        $this->assertEquals(0, $consumer->ack($job));
    }


    public function testFastAck()
    {
        $queue = 'test-'.substr(sha1(mt_rand()), 6);
        $consumer= new Consumer($this->stream);
        $producer= new Producer($this->stream);
        $job = $producer->addJob($queue, Job::create(['body' => '42']));
        $this->assertEquals(1, $consumer->fastAck($job));
        $this->assertEquals(0, $consumer->fastAck($job));
    }


    public function testPeek()
    {
        $queue = 'test-'.substr(sha1(mt_rand()), 6);
        $consumer= new Consumer($this->stream);
        $producer= new Producer($this->stream);
        $this->assertEmpty($consumer->peek($queue));
        $job = $producer->addJob($queue, Job::create(['body' => 'test-peek']));
        list($peekedJob) = $consumer->peek($queue);
        $this->assertEquals($peekedJob->getId(), $job->getId());
    }
}
