<?php
namespace Phloppy;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

use Phloppy\Exception\CommandException;
use Phloppy\Exception\ConnectException;

class Consumer extends Client {


    /**
     * @param string|string[] $queues
     * @param int $count
     * @param int $timeoutMs
     * @return Job[]
     */
    public function getJobs($queues, $count = 1, $timeoutMs = 200)
    {
        $jobs = [];

        $rsp = $this->send(array_merge([
            'GETJOB',
            'TIMEOUT',
            $timeoutMs,
            'COUNT',
            (int) $count,
            'FROM',
        ], (array) $queues));

        if (!is_array($rsp)) {
            return $jobs;
        }

        foreach($rsp as $job) {
            $jobs[] = Job::create([
                'id' => $job[1],
                'body' => $job[2]
            ]);
        }

        return $jobs;
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
