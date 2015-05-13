<?php

namespace Phloppy\Client;

use Phloppy\AbstractIntegrationTest;
use Phloppy\Job;

class QueueIntegrationTest extends AbstractIntegrationTest {

    public function testLen()
    {
        $queueName = 'test-'. substr(sha1(mt_rand()), 0, 6);
        $queue = new Queue($this->stream, $this->log);
        $producer = new Producer($this->stream, $this->log);
        $consumer = new Consumer($this->stream, $this->log);
        $job = new Job('job-body');

        $this->assertSame(0, $queue->len($queueName));
        $job->setRetry(1);
        $producer->addJob($queueName, $job);
        $this->assertSame(1, $queue->len($queueName));
        $consumedJob = $consumer->getJob($queueName);
        $this->assertSame(0, $queue->len($queueName));

        $this->assertEquals($job->getId(), $consumedJob->getId());
        $this->assertEquals($job->getQueue(), $consumedJob->getQueue());

        // should be retried after 1 second
        usleep(1.5E6);
        $this->assertSame(1, $queue->len($queueName));
        $retriedJob = $consumer->getJob($queueName);
        $this->assertSame(0, $queue->len($queueName));

        $this->assertEquals($retriedJob, $consumedJob);
    }

    public function testPeek()
    {
        $queueName = 'test-'.substr(sha1(mt_rand()), 0, 6);
        $queue= new Queue($this->stream);
        $producer= new Producer($this->stream);
        $this->assertEmpty($queue->peek($queueName));
        $job = $producer->addJob($queueName, Job::create(['body' => 'test-peek']));
        list($peekedJob) = $queue->peek($queueName);
        $this->assertEquals($peekedJob->getId(), $job->getId());
    }
}
