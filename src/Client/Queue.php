<?php

namespace Phloppy\Client;

use Phloppy\Job;

/**
 * Queue introspection commands.
 */
class Queue extends AbstractClient {

    /**
     * Returns the length of the queue.
     *
     * @param $queue
     * @return int
     */
    public function len($queue)
    {
        return $this->send(['QLEN', $queue]);
    }


    /**
     * Peek count jobs from the given queue without removing them.
     *
     * @param string $queue
     * @param int $count
     *
     * @return Job[]
     */
    public function peek($queue, $count = 1)
    {
        return $this->mapJobs($this->send(['QPEEK', $queue, $count]));
    }
}
