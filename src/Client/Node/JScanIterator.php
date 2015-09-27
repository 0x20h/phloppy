<?php

namespace Phloppy\Client\Node;

use Phloppy\Client\AbstractClient;
use Phloppy\Stream\StreamInterface;
use Psr\Log\LoggerInterface;

/**
 * JSCAN Iterator.
 *
 * @see https://github.com/antirez/disque#jscan-cursor-count-count-busyloop-queue-queue-state-state1-state-state2--state-staten-reply-allid
 */
class JScanIterator extends AbstractClient implements \Iterator {

    const FORMAT_ID  = 'id';
    const FORMAT_ALL = 'all';

    /**
     * @var int Current disque cursor.
     */
    private $cursor = -1;

    /**
     * @var array returned elements.
     */
    private $elements = [];

    /**
     * @var int current iterator index.
     */
    private $index = 0;

    /**
     * Pagination count per call.
     *
     * @var int
     */
    private $count = 100;

    /**
     * @var string[]
     */
    private $queues = [];

    /**
     * @var string[]
     */
    private $states = [];

    /**
     * @var string
     */
    private $format = self::FORMAT_ID;


    /**
     * @param StreamInterface $stream
     * @param LoggerInterface|null $log
     */
    public function __construct(StreamInterface $stream, LoggerInterface $log = null)
    {
        parent::__construct($stream, $log);
    }

    private function scan()
    {
        // initialize cursor
        if ($this->cursor < 0) {
            $this->cursor = 0;
        }

        // Iterating here because the response of the jscan
        // iteration might be empty due to filter restrictions
        // (disque internally limits the number of dictScan iterations).
        // Thus we rescan if we didn't get any elements and the cursor
        // is still valid.
        do {
            $command = ['JSCAN', $this->cursor];

            if ($this->count) {
                $command = array_merge($command, ['COUNT', $this->count]);
            } else {
                $command[] = 'BUSYLOOP';
            }

            if (!empty($this->queues)) {
                $command = array_reduce($this->queues, function($prev, $current) {
                    return array_merge($prev, ['QUEUE', $current]);
                }, $command);
            }


            if (!empty($this->states)) {
                $command = array_reduce($this->states, function($prev, $current) {
                    return array_merge($prev, ['STATE', $current]);
                }, $command);
            }

            $command        = array_merge($command, ['REPLY', $this->format]);
            $response       = $this->send($command);
            $this->cursor   = (int) $response[0];
            $this->elements = array_merge($this->elements, $response[1]);
        } while ($this->cursor && empty($response[1]));
    }


    /**
     * Return the current element
     *
     * @link http://php.net/manual/en/iterator.current.php
     * @return string the next queue name
     */
    public function current()
    {
        return $this->elements[$this->index];
    }

    /**
     * Move forward to next element
     *
     * @link http://php.net/manual/en/iterator.next.php
     */
    public function next()
    {
        $this->index++;
    }

    /**
     * Return the key of the current element
     *
     * @link http://php.net/manual/en/iterator.key.php
     * @return int scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->index;
    }

    /**
     * Checks if current position is valid
     *
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean true on success or false on failure.
     */
    public function valid()
    {
        // initialize/refresh cursor
        if ($this->cursor != 0 && !isset($this->elements[$this->index])) {
            $this->scan();
        }

        return isset($this->elements[$this->index]);
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     */
    public function rewind()
    {
        $this->index = 0;
    }


    /**
     * @param int $count
     */
    public function setCount($count)
    {
        $this->count = (int) $count;
    }


    /**
     * @param string[] $queues
     */
    public function setQueues($queues)
    {
        $this->queues = $queues;
    }


    /**
     * @param string[] $states
     */
    public function setStates($states)
    {
        $this->states = $states;
    }


    /**
     * @param string $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }
}
