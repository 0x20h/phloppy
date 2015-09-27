<?php

namespace Phloppy\Client;

use Iterator;
use Phloppy\Client\Queue\QScanIterator;
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
        return (int) $this->send(['QLEN', $queue]);
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


    /**
     * Scan all existing queues.
     *
     * Options may be used to filter the scan results.
     *
     * @param int $count Return count elements per call. A count of 0 implies returning all elements at once.
     * @param int $min Filter queues with at least min elements.
     * @param int $max Filter queues with at most max elements.
     * @param int $rate Filter queues by job import rate.
     *
     * @return Iterator
     */
    public function scan($count = 50, $min = 0, $max = 0, $rate = 0)
    {
        $iterator = new QScanIterator($this->stream, $this->log);
        $iterator->setCount($count)
            ->setMin($min)
            ->setMax($max)
            ->setRate($rate);

        return $iterator;
    }


    /**
     * Enqueue the given job Ids.
     *
     * @param string[] $jobIds
     * @return int
     */
    public function enqueue(array $jobIds)
    {
        return (int) $this->send(array_merge(['ENQUEUE'], $jobIds));
    }


    /**
     * Dequeue the given job Ids.
     *
     * @param string[] $jobIds
     * @return int
     */
    public function dequeue(array $jobIds)
    {
        return (int) $this->send(array_merge(['DEQUEUE'], $jobIds));
    }
}
