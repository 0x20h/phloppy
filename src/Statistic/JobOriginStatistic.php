<?php

namespace Phloppy\Statistic;

use Phloppy\Exception;
use Phloppy\Job;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

/**
 * Collect Statistics of jobs/sec by node.
 */
class JobOriginStatistic implements NodeStatistic
{
    /**
     * Node Ids
     *
     * @var \DateTime[]
     */
    private $lastReceived;

    /**
     * 2-dimensional array storing rates for (queue, nodeId) pairs
     * @var string[]
     */
    private $stats;

    /**
     * @var LoggerInterface
     */
    private $log;

    /**
     * @var float
     */
    private $alpha;


    /**
     * @param float           $alpha Sensitivity factor.
     * @param LoggerInterface $log
     */
    function __construct($alpha = 0.9, LoggerInterface $log = null)
    {
        if (!$log) {
            $log = new NullLogger();
        }

        if ((float) $alpha < 0.5 || (float) $alpha > 1.0) {
            throw new InvalidArgumentException('0.5 < $alpha < 1.0');
        }

        $this->alpha = (float) $alpha;
        $this->log = $log;
    }


    /**
     * Update jobs by node/sec rt-statistic.
     *
     * @param Job       $job
     *
     * @param \DateTime $receivedAt
     *
     * @return float messages/sec from the given node
     */
    public function update(Job $job, \DateTime $receivedAt = null)
    {
        $nodeId = $job->getOriginNode();
        $queue = $job->getQueue();

        if (!$receivedAt) {
            $receivedAt = new \DateTime('now');
        }

        if (!isset($this->lastReceived[$queue][$nodeId])) {
            $this->lastReceived[$queue][$nodeId] = clone $receivedAt;
        }

        if (!isset($this->stats[$queue])) {
            $this->stats[$queue] = [];
        }

        if (!isset($this->stats[$queue][$nodeId])) {
            $this->stats[$queue][$nodeId] = 0.;
        }

        $secs = $receivedAt->diff($this->lastReceived[$queue][$nodeId])->s;

        if ($secs < 0) {
            $secs = 0;
        }

        $this->lastReceived[$queue][$nodeId] = clone $receivedAt;
        // exp moving average from the last message to now (per sec)
        $this->stats[$queue][$nodeId] =
            pow($this->alpha, $secs) * $this->stats[$queue][$nodeId] +
            (1 - $this->alpha);

        $this->log->debug('updated value', [
            'id' => $nodeId,
            'queue' => $queue,
            'value' => $this->stats[$queue][$nodeId],
            'delta' => $secs
        ]);

        return $this->stats[$queue][$nodeId];
    }


    /**
     * Return an ordered list of nodes.
     *
     * The list is ordered descending by the frequency of Jobs.
     *
     * @return float[]
     */
    public function nodes($queue)
    {
        assert(arsort($this->stats[$queue]), true);
        return $this->stats[$queue];
    }


    /**
     * Return information on a specific (queue, node).
     *
     * If the queue and/or node is not known, no messages have been retrieved from it, so 0. will be returned.
     *
     * @param string $queue
     * @param string $nodeId
     *
     * @return float msg rate (per sec)
     */
    public function node($queue, $nodeId)
    {
        return isset($this->stats[$queue][$nodeId]) ? $this->stats[$queue][$nodeId] : 0.;
    }
}