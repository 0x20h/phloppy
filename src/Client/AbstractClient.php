<?php
namespace Phloppy\Client;

use Phloppy\Exception\CommandException;
use Phloppy\Job;
use Phloppy\RespUtils;
use Phloppy\Stream\StreamException;
use Phloppy\Stream\StreamInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\ImmutableEventDispatcher;

/**
 * Abstract Disque Client.
 */
abstract class AbstractClient {

    /**
     * @var StreamInterface
     */
    protected $stream;

    /**
     * @var LoggerInterface
     */
    protected $log;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;


    /**
     * @param StreamInterface      $stream
     * @param LoggerInterface|null $log Logger instance.
     */
    public function __construct(
        StreamInterface $stream,
        LoggerInterface $log = null,
        EventDispatcherInterface $dispatcher = null
    ) {
        if (!$log) {
            $log = new NullLogger();
        }

        if (!$dispatcher) {
            $dispatcher = new EventDispatcher();
        }

        $this->log = $log;
        $this->stream = $stream;
        $this->dispatcher = $dispatcher;
    }


    /**
     * @return EventDispatcherInterface
     */
    public function getDispatcher()
    {
        return new $this->dispatcher;
    }


    /**
     * Send request and retrieve response to the connected disque node.
     *
     * @param array $args
     *
     * @return array|int|null|string
     *
     * @throws CommandException
     * @throws StreamException
     */
    protected function send(array $args = [])
    {
        $this->log->debug('send()ing command', $args);
        $response = RespUtils::deserialize($this->stream->write(RespUtils::serialize($args)));
        $this->log->debug('response', [$response]);

        return $response;
    }


    /**
     * Map Disque's job responses to Job objects.
     *
     * @param array $list Job response array from the disque server.
     *
     * @return Job[]
     */
    protected function mapJobs(array $list)
    {
        return array_map(
            function ($element) {
                return Job::create([
                    'queue' => $element[0],
                    'id' => $element[1],
                    'body' => isset($element[2]) ? $element[2] : '',
                ]);
            },
            $list
        );
    }
}
