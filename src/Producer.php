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
    private $replication_timeout = 2000;

    /**
     * Replication factor for jobs.
     *
     * @var int
     */
    private $replication_factor = 1;


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
            $this->replication_timeout,
            'REPLICATE', $this->replication_factor,
            'DELAY', $job->getDelay(),
            'RETRY', $job->getRetry(),
            'TTL', $job->getTTL()
        ]);

        $job->setId($id);
        return $job;
    }

    /**
     * @return int
     */
    public function getReplicationTimeout()
    {
        return $this->replication_timeout;
    }

    /**
     * @param int $replication_timeout
     */
    public function setReplicationTimeout($replication_timeout)
    {
        $this->replication_timeout = $replication_timeout;
    }

    /**
     * @return int
     */
    public function getReplicationFactor()
    {
        return $this->replication_factor;
    }

    /**
     * @param int $replication_factor
     */
    public function setReplicationFactor($replication_factor)
    {
        $this->replication_factor = $replication_factor;
    }
}
