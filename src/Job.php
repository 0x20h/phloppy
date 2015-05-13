<?php
namespace Phloppy;

class Job {

    /**
     * Job Id (generated by disque).
     *
     * @var string
     */
    private $id = '';


    /**
     * Queue name this job was fetched from.
     *
     * @var string
     */
    private $queue = '';


    /**
     * Job body.
     *
     * @var string
     */
    private $body = '';


    /**
     * Delay time (before putting the job into the queue, in seconds).
     *
     * @var int
     */
    private $delay = 0;


    /**
     * Retry time (seconds).
     *
     * How much time should elapse, since the last time the job was queued, and without an acknowledge about the job
     * delivery, before the job is re-queued again for delivery.
     *
     * @var int
     */
    private $retry = 120;


    /**
     * The expire time (in seconds).
     *
     * How much time should elapse for the job to be deleted regardless of the fact it was successfully delivered,
     * i.e. acknowledged, or not.
     *
     * @var int
     */
    private $ttl = 3600;


    public function __construct($body)
    {
        $this->body = $body;
    }


    /**
     * Return the job id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return int
     */
    public function getDelay()
    {
        return $this->delay;
    }

    /**
     * @param int $delay
     */
    public function setDelay($delay)
    {
        $this->delay = (int) $delay;
    }

    /**
     * @return int
     */
    public function getRetry()
    {
        return $this->retry;
    }

    /**
     * @param int $retry
     */
    public function setRetry($retry)
    {
        $this->retry = $retry;
    }

    /**
     * @return int
     */
    public function getTtL()
    {
        return $this->ttl;
    }

    /**
     * @param mixed $ttl
     */
    public function setTtl($ttl)
    {
        $this->ttl = $ttl;
    }

    /**
     * @return string
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * @param string $queue
     */
    public function setQueue($queue)
    {
        $this->queue = $queue;
    }


    /**
     * Job Factory method.
     *
     * @param array $args
     * @return Job
     */
    public static function create(array $args)
    {
        $job = new Job($args['body']);

        if (isset($args['id'])) {
            $job->setId($args['id']);
            $job->setTtl(hexdec(substr($job->getId(), -4)));
        }

        if (isset($args['queue'])) {
            $job->setQueue($args['queue']);
        }

        if (isset($args['ttl'])) {
            $job->setTtl($args['ttl']);
        }

        // @TODO add other variables
        return $job;
    }

    /**
     * Retrieve the originating node id.
     *
     * @return string|null The node id of the node where the job was published
     */
    public function getOriginNode() {
       return $this->id ? substr($this->id, 3, 11) : null;
    }

    public function __toString()
    {
        return $this->getId();
    }
}
