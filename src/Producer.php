<?php
namespace Disque;

use Disque\Stream\DefaultStream;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

use Disque\Exception\CommandException;
use Disque\Exception\ConnectException;

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


}
