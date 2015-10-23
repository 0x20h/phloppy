<?php

namespace Phloppy\Statistic;

use Phloppy\Exception;
use Phloppy\Job;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class JobOriginStatistic implements NodeStatistic
{

    const ALPHA = 0.9;

    /**
     * Node Ids
     *
     * @var \DateTime[]
     */
    private $lastReceived;

    /**
     * @var float[]
     */
    private $stats;

    /**
     * @var LoggerInterface
     */
    private $log;

    function __construct(LoggerInterface $log = null)
    {
        if (!$log) {
            $log = new NullLogger();
        }

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

        if (!$receivedAt) {
            $receivedAt = new \DateTime('now');
        }

        if (!isset($this->lastReceived[$nodeId])) {
            $this->lastReceived[$nodeId] = clone $receivedAt;
        }

        if (!isset($this->stats[$nodeId])) {
            $this->stats[$nodeId] = 0.;
        }

        $secs = $receivedAt->diff($this->lastReceived[$nodeId])->s;

        if ($secs < 0) {
            $secs = 0;
        }

        $this->lastReceived[$nodeId] = clone $receivedAt;
        // exp moving average from the last message to now (per sec)
        $this->stats[$nodeId] =
            pow(self::ALPHA, $secs) * $this->stats[$nodeId] +
            (1 - self::ALPHA);

        $this->log->debug('updated value', ['id' => $nodeId, 'pow' => pow(self::ALPHA, $secs), 'value' => $this->stats[$nodeId], 'delta' => $secs]);
        return $this->stats[$nodeId];
    }


    /**
     * Return an ordered list of nodes.
     *
     * The list is ordered descending by the frequency of Jobs.
     *
     * @return float[]
     */
    public function nodes()
    {
        return $this->stats;
    }


    /**
     * Return information on a specific node.
     *
     * If the node is not known, no messages have been retrieved from it, so 0. will be returned.
     *
     * @param $nodeId
     *
     * @return float
     */
    public function node($nodeId)
    {
        return isset($this->stats[$nodeId]) ? $this->stats[$nodeId] : 0.;
    }
}