<?php
namespace Phloppy\Client;

use Phloppy\Job;
use Phloppy\RespUtils;
use Phloppy\Stream\StreamInterface;
use Phloppy\Stream\StreamException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

use Phloppy\Exception\CommandException;

/**
 * General disque Client.
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
     * @param StreamInterface $stream
     * @param LoggerInterface|null $log Logger instance.
     */
    public function __construct(StreamInterface $stream, LoggerInterface $log = null)
    {
        if (!$log) {
            $log = new NullLogger();
        }

        $this->log    = $log;
        $this->stream = $stream;
    }


    /**
     * Send request and retrieve response to the connected disque node.
     *
     * @param array $args
     * @return array|int|null|string
     *
     * @throws CommandException
     */
    protected function send(array $args = [])
    {
        $this->log->debug('send()ing command', $args);

        try {
            $response = RespUtils::deserialize($this->stream->write(RespUtils::serialize($args)));
            $this->log->debug('response', [$response]);
            return $response;
        } catch(StreamException $e) {
            $this->log->warning($e->getMessage());
        }

        return null;
    }


    /**
     * Map Disque's job responses to Job objects.
     *
     * @param array $list Job response array from the disque server.
     * @return Job[]
     */
    protected function mapJobs(array $list) {
        return array_map(
            function($element) { return Job::create([
                'queue' => $element[0],
                'id'    => $element[1],
                'body'  => isset($element[2]) ? $element[2] : '',
            ]); },
            $list
        );
    }
}
