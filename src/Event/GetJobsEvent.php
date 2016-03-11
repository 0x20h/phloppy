<?php

namespace Phloppy\Event;

use Symfony\Component\EventDispatcher\Event;

class GetJobsEvent extends Event
{

    const ID = 'job.getjob';

    /**
     * @var string[]
     */
    private $queues;


    /**
     * @param string[] $queues
     * @param          $count
     * @param          $timeout
     */
    public function __construct(array $queues, $count, $timeout)
    {
        $this->queues = $queues;
    }


    /**
     * @return string[]
     */
    public function getQueues()
    {
        return $this->queues;
    }

}