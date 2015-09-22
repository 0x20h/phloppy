<?php

namespace Phloppy\Client\Queue;

use Phloppy\Client\AbstractClient;
use Phloppy\Stream\StreamInterface;
use Psr\Log\LoggerInterface;

/**
 * QSCAN Iterator.
 *
 * For the command options see https://github.com/antirez/disque
 */
class QScanIterator extends AbstractClient implements \Iterator {

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
     * @var int
     */
    private $min = 0;

    /**
     * @var int
     */
    private $max = 0;

    /**
     * @var int
     */
    private $rate = 0;


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

        // Iterating here because the response of the qscan
        // iteration might be empty due to filter restrictions
        // (disque internally limits the number of dictScan iterations).
        // Thus we rescan if we didn't get any elements and the cursor
        // is still valid.
        do {
            $command = ['QSCAN', $this->cursor];

            if ($this->count) {
                $command = array_merge($command, ['COUNT', $this->count]);
            } else {
                $command[] = 'BUSYLOOP';
            }

            if ($this->min) {
                $command = array_merge($command, ['MINLEN', $this->min]);
            }


            if ($this->max) {
                $command = array_merge($command, ['MAXLEN', $this->max]);
            }

            if ($this->rate) {
                $command = array_merge($command, ['IMPORTRATE', $this->rate]);
            }

            $response       = $this->send($command);
            $this->cursor   = (int) $response[0];
            $this->elements = array_merge($this->elements, $response[1]);
        } while($this->cursor && empty($response[1]));
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
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param int $count
     * @return QScanIterator
     */
    public function setCount($count)
    {
        $this->count = (int) $count;
        return $this;
    }

    /**
     * @return int
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * Set the minimum queue size.
     *
     * @param int $min min >= 0
     * @return QScanIterator
     */
    public function setMin($min)
    {
        $this->min = (int) $min;
        return $this;
    }

    /**
     * @return int
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @param int $max
     * @return QScanIterator
     */
    public function setMax($max)
    {
        $this->max = (int) $max;
        return $this;
    }

    /**
     * @return int
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * @param int $rate
     * @return QScanIterator
     */
    public function setRate($rate)
    {
        $this->rate = $rate;
        return $this;
    }
}
