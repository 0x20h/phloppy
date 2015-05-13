<?php
namespace Phloppy\Client;

use Phloppy\Client;
use Phloppy\Job;

/**
 * Consumer client implementation.
 */
class Consumer extends Client {

    /**
     * @param string|string[] $queues
     * @param int $count
     * @param int $timeoutMs
     * @return Job[]
     */
    public function getJobs($queues, $count = 1, $timeoutMs = 200)
    {
        return $this->mapJobs($this->send(array_merge(
            [
                'GETJOB',
                'TIMEOUT',
                $timeoutMs,
                'COUNT',
                (int) $count,
                'FROM',
            ],
            (array) $queues
        )));
    }


    /**
     * Retrieve a single job from the given queues
     * @param string|string[] $queues
     * @param int $timeoutMs
     * @return Job|null
     */
    public function getJob($queues, $timeoutMs = 200) {
        $jobs = $this->getJobs($queues, 1, $timeoutMs);

        if(empty($jobs)) {
            return null;
        }

        return $jobs[0];
    }


    /**
     * Acknowledge a job execution.
     *
     * @param Job $job
     *
     * @return int Number of Jobs acknowledged.
     */
    public function ack(Job $job)
    {
        assert($job->getId() != null);
        return (int) $this->send(['ACKJOB', $job->getId()]);
    }


    /**
     * Fast Acknowledge a job execution.
     *
     * @param Job $job
     *
     * @return int Number of Jobs acknowledged.
     * @see https://github.com/antirez/disque#fast-acknowledges
     */
    public function fastAck(Job $job)
    {
        assert($job->getId() != null);
        return (int) $this->send(['FASTACK', $job->getId()]);
    }
}
