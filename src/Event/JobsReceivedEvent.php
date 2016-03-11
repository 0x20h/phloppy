<?php

namespace Phloppy\Event;

use Symfony\Component\EventDispatcher\Event;

class JobsReceivedEvent extends Event
{
    const ID = 'job.getjob';

    /**
     * @var Job[]
     */
    private $jobs;

    /**
     * @var string[]
     */
    private $queues;


    /**
     * @param string[] $queues
     * @param Job[] $jobs
     */
    public function __construct(array $queues, array $jobs)
    {
        $this->queues = $queues;
        $this->jobs = $jobs;
    }


    /**
     * @return Job[]
     */
    public function getJobs()
    {
        return $this->jobs;
    }


    /**
     * @return string[]
     */
    public function getQueues()
    {
        return $this->queues;
    }

}