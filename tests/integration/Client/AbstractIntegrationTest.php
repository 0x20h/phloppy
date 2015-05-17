<?php

namespace Phloppy\Client;

use Phloppy\Exception\ConnectException;
use Phloppy\Stream\Pool;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class AbstractIntegrationTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var \Phloppy\Stream\Pool
     */
    protected $stream;

    /**
     * @var LoggerInterface
     */
    protected $log;

    protected function setUp()
    {
        if (empty($_ENV['DISQUE_SERVERS'])) {
            return $this->markTestSkipped('no disque servers configured');
        }

        try {
            $servers = explode(',', $_ENV['DISQUE_SERVERS']);
            $this->log = new NullLogger();
            //$this->log = new \Monolog\Logger(new \Monolog\Handler\StreamHandler('php://stdout'));
            $this->stream = new Pool($servers, $this->log);
        } catch (ConnectException $e) {
            $this->markTestSkipped($e->getMessage());
        }
    }

    protected function tearDown()
    {
        if ($this->stream) {
            $this->stream->close();
        }
    }
}
