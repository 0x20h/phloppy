<?php
namespace Phloppy;

use Phloppy\Stream\DefaultStream;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

use Phloppy\Exception\CommandException;
use Phloppy\Exception\ConnectException;

class Producer extends Client {

    /**
     * Timeout in ms that disque should block the client while replicating the job.
     *
     * @var int
     */
    private $replicationTimeout = 2000;

    /**
     * Replication factor for jobs.
     *
     * @var int
     */
    private $replicationFactor = 1;


    /**
     * Enqueue the given job.
     *
     * @param $queue
     * @param Job $job
     * @return Job The updated job (e.g. ID set).
     */
    public function addJob($queue, Job $job)
    {
        $id = $this->send([
            'ADDJOB',
            $queue,
            $job->getBody(),
            $this->replicationTimeout,
            'REPLICATE', $this->replicationFactor,
            'DELAY', $job->getDelay(),
            'RETRY', $job->getRetry(),
            'TTL', $job->getTtL()
        ]);

        $job->setId($id);
        return $job;
    }

    /**
     * @return int
     */
    public function getReplicationTimeout()
    {
        return $this->replicationTimeout;
    }

    /**
     * @param int $replicationTimeout
     */
    public function setReplicationTimeout($replicationTimeout)
    {
        $this->replicationTimeout = $replicationTimeout;
    }

    /**
     * @return int
     */
    public function getReplicationFactor()
    {
        return $this->replicationFactor;
    }

    /**
     * @param int $replicationFactor
     */
    public function setReplicationFactor($replicationFactor)
    {
        $this->replicationFactor = $replicationFactor;
    }
}
