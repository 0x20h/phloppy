<?php

namespace Phloppy\Client;

use Phloppy\Job;

class QueueIntegrationTest extends AbstractIntegrationTest {

    public function testLen()
    {
        $queueName = 'test-'. substr(sha1(mt_rand()), 0, 6);
        $queue = new Queue($this->stream);
        $producer = new Producer($this->stream);
        $consumer = new Consumer($this->stream);
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

    public function testScan()
    {
        $queueName           = 'test-' . substr(sha1(mt_rand()), 0, 6);
        $oneElementQueueName = 'test-'. substr(sha1(mt_rand()), 0, 6);
        $buffer              = [];
        $jobs                = [];
        $i                   = 0;

        $queue    = new Queue($this->stream);
        $producer = new Producer($this->stream);
        $consumer = new Consumer($this->stream);

        $this->assertSame(0, $queue->len($queueName));

        $jobs[] = $producer->addJob($queueName, Job::create(['body' => 'test-scan']));
        $jobs[] = $producer->addJob($queueName, Job::create(['body' => 'test-scan']));
        $jobs[] = $producer->addJob($queueName, Job::create(['body' => 'test-scan']));
        $jobs[] = $producer->addJob($oneElementQueueName, Job::create(['body' => 'test-scan']));

        $iterator = $queue->scan(0, 3, 1000, 0);
        $this->assertTrue($iterator->valid());
        $first = $iterator->current();

        while($iterator->valid()) {
            $buffer[] = $iterator->current();
            $this->assertSame($i++, $iterator->key());
            $iterator->next();
        }

        $this->assertContains($queueName, $buffer);
        $this->assertNotContains($oneElementQueueName, $buffer);
        $iterator->rewind();
        $this->assertSame($first, $iterator->current());

        // cleanup
        foreach($jobs as $job) {
            $this->assertSame(1, $consumer->ack($job));
        }
    }


    public function testScanNonBlocking()
    {
        $queueName           = 'test-' . substr(sha1(mt_rand()), 0, 6);
        $oneElementQueueName = 'test-'. substr(sha1(mt_rand()), 0, 6);
        $buffer              = [];
        $jobs                = [];
        $i                   = 0;

        $queue    = new Queue($this->stream);
        $producer = new Producer($this->stream);
        $consumer = new Consumer($this->stream);

        $this->assertSame(0, $queue->len($queueName));

        $jobs[] = $producer->addJob($queueName, Job::create(['body' => 'test-scan']));
        $jobs[] = $producer->addJob($queueName, Job::create(['body' => 'test-scan']));
        $jobs[] = $producer->addJob($queueName, Job::create(['body' => 'test-scan']));
        $jobs[] = $producer->addJob($oneElementQueueName, Job::create(['body' => 'test-scan']));

        $iterator = $queue->scan(2, 3);

        foreach($iterator as $k => $v) {
            $buffer[] = $v;
        }

        $this->assertContains($queueName, $buffer);
    }
}
