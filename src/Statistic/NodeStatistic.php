<?php

namespace Phloppy\Statistic;

use Phloppy\Job;

interface NodeStatistic
{

    /**
     * Update node stats by providing a Job.
     *
     * @param Job       $job
     *
     * @param \DateTime $receivedAt
     *
     * @return float messages/sec from the node that produced the job
     */
    public function update(Job $job, \DateTime $receivedAt = null);


    /**
     * Return an ordered list of nodes.
     *
     * The list is ordered descending by the frequency of Jobs.
     *
     * @return float[]
     */
    public function nodes();


    /**
     * Return information on a specific node.
     *
     * If the node is not known, no messages have been retrieved from it, so 0. will be returned.
     *
     * @param $nodeId
     *
     * @return float
     */
    public function node($nodeId);
}