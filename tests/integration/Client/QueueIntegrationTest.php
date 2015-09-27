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


    public function testEnqueueDequeue()
    {
        $queueName = 'test-'.substr(sha1(mt_rand()), 0, 6);
        $queue     = new Queue($this->stream);
        $producer  = new Producer($this->stream);
        $node      = new Node($this->stream);

        $this->assertEquals(0, $queue->len($queueName));
        $job1 = $producer->addJob($queueName, Job::create(['body' => 'test-enq-deq-1']));
        $job2 = $producer->addJob($queueName, Job::create(['body' => 'test-enq-deq-2']));

        $this->assertEquals(2, $queue->len($queueName));
        // dequeue one of the jobs
        $this->assertEquals(1, $queue->dequeue([$job1->getId()]));
        // now only one job left in queue
        $this->assertEquals(1, $queue->len($queueName));
        // job2 still enqueued, so none should be enqueued
        $this->assertEquals(0, $queue->enqueue([$job2->getId()]));
        // job1 is enqueued again, so 1 should be returned
        $this->assertEquals(1, $queue->enqueue([$job1->getId()]));
        // and the queue has len 2 again
        $this->assertEquals(2, $queue->len($queueName));

        // cleanup
        $this->assertEquals(2, $node->del([$job1->getId(), $job2->getId()]));
    }
}
