<?php
namespace Phloppy\Client;

use Phloppy\Client;
use Phloppy\Job;

/**
 * Producer client implementation.
 */
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
     * @param string $queue
     * @param Job    $job
     * @param int    $maxlen specifies that if there are already count messages queued for the specified queue name,
     *                       the message is refused and an error reported to the client.
     * @oaram bool   $async asks the server to let the command return ASAP and replicate the job to other nodes in the
     *                      background. The job gets queued ASAP, while normally the job is put into the queue only when
     *                      the client gets a positive reply.
     *
     * @return Job          The updated job (e.g. ID set).
     */
    public function addJob($queue, Job $job, $maxlen = 0, $async = false)
    {
        $command = [
            'ADDJOB',
            $queue,
            $job->getBody(),
            $this->getReplicationTimeout(),
            'REPLICATE', $this->getReplicationFactor(),
            'DELAY', $job->getDelay(),
            'RETRY', $job->getRetry(),
            'TTL', $job->getTtL(),
        ];

        if ($maxlen) {
            $command[] = 'MAXLEN';
            $command[] = (int) $maxlen;
        }

        if ($async) {
            $command[] = 'ASYNC';
        }

        $id = $this->send($command);
        $job->setId($id);
        $job->setQueue($queue);
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
