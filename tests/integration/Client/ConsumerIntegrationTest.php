<?php

namespace Phloppy\Client;

use Phloppy\Job;

class ConsumerIntegrationTest extends AbstractIntegrationTest {

    public function testGetJob()
    {
        $queue = 'test-'.substr(sha1(mt_rand()), 0, 6);
        $client= new Consumer($this->stream);
        $job = $client->getJob($queue, 1);
        $this->assertNull($job);
    }

    public function testAckUnknownJob()
    {
        $queue = 'test-'.substr(sha1(mt_rand()), 0, 6);
        $client= new Consumer($this->stream);
        // ack an unknown job
        $job = Job::create(['id' => 'D-dcb833cf-8YL1NT17e9+wsA/09NqxscTI-05a1', 'body' => 'foo']);
        $this->assertEquals(0, $client->ack($job));
    }


    public function testAckNewJob()
    {
        $queue = 'test-'.substr(sha1(mt_rand()), 0, 6);

        $consumer= new Consumer($this->stream);
        $producer= new Producer($this->stream);
        $job = $producer->addJob($queue, Job::create(['body' => '42']));
        $consumer->getJob($queue);
        $this->assertEquals(1, $consumer->ack($job));
        $this->assertEquals(0, $consumer->ack($job));
    }


    public function testFastAck()
    {
        $queue = 'test-'.substr(sha1(mt_rand()), 0, 6);
        $consumer= new Consumer($this->stream);
        $producer= new Producer($this->stream);
        $job = $producer->addJob($queue, Job::create(['body' => '42']));
        $this->assertEquals(1, $consumer->fastAck($job));
        $this->assertEquals(0, $consumer->fastAck($job));
    }


    public function testShow()
    {
        $queue = 'test-'.substr(sha1(mt_rand()), 0, 6);
        $consumer = new Consumer($this->stream);
        $producer = new Producer($this->stream);
        $job      = $producer->addJob($queue, Job::create(['body' => __METHOD__]));
        $findJob  = $consumer->show($job->getId());
        $this->assertEquals($findJob->getBody(), $job->getBody());
        $findJob  = $consumer->show('D-dcb833cf-8YL1NT17e9+wsA/09NqxscQI-00a1');
        $this->assertNull($findJob);
    }


    /**
     * @expectedException \Phloppy\Exception\CommandException
     * @expectedExceptionMessage BADID Invalid Job ID format.
     */
    public function testShowBadJobId()
    {
        $consumer = new Consumer($this->stream);
        $consumer->show('foo');
    }


    public function testNack()
    {
        $queue = 'test-'.substr(sha1(mt_rand()), 0, 6);
        $consumer = new Consumer($this->stream);
        $producer = new Producer($this->stream);

        // unknown jobid
        $this->assertEquals(0, $consumer->nack(['D-dcb833cf-8YL1NT17e9+wsA/09NqxscQI-0551']));

        $job = $producer->addJob($queue, Job::create(['body' => __METHOD__]));
        $jobConsumed = $consumer->getJob($queue);
        $this->assertSame($job->getId(), $jobConsumed->getId());
        $this->assertEquals(1, $consumer->nack([$jobConsumed->getId()]));


        // NACK'd job should be reinserted
        $nackdJob = $consumer->getJob($queue);
        $this->assertEquals($job->getId(), $nackdJob->getId());

        // cleanup
        $this->assertEquals(1, $consumer->ack($nackdJob));
    }


    public function testWorking()
    {
        $retry = rand(30, 60);
        $queue = 'test-'.substr(sha1(mt_rand()), 0, 6);
        $consumer = new Consumer($this->stream);
        $producer = new Producer($this->stream);
        $producer->addJob($queue, Job::create(['body' => __METHOD__, 'retry' => $retry, 'ttl' => 600]));
        $job = $consumer->getJob($queue);
        $this->assertEquals($retry, $consumer->working($job));
    }

}
