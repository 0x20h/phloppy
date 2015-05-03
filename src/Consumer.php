<?php
namespace Disque;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

use Disque\Exception\CommandException;
use Disque\Exception\ConnectException;

class Consumer extends Client {


    /**
     * @param string|string[] $queues
     * @param int $count
     * @param int $timeout_ms
     * @return Job[]
     */
    public function getJobs($queues, $count = 1, $timeout_ms = 200)
    {
        $jobs = [];

        $rsp = $this->send(array_merge([
            'GETJOB',
            'TIMEOUT',
            $timeout_ms,
            'COUNT',
            (int) $count,
            'FROM',
        ], (array) $queues));

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
     * @param int $timeout_ms
     * @return Job|null
     */
    public function getJob($queues, $timeout_ms = 200) {
        $jobs = $this->getJobs($queues, 1, $timeout_ms);

        if(!$jobs) {
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
        return $this->send(['ACKJOB', $job->getId()]);
    }


    public function fastAck(Job $job)
    {
        assert($job->getId() != null);
        return $this->send(['FASTACK', $job->getId()]);
    }
}
