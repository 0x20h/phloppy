<?php

namespace Phloppy\Client;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Phloppy\Exception\ConnectException;
use Phloppy\Stream\Pool;
use Phloppy\Stream\StreamInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class AbstractIntegrationTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var StreamInterface
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

            if (!$this->log) {
                $this->log = new NullLogger();

                if (!empty($_ENV['LOGFILE'])) {
                    $this->log = new Logger('tests', [new StreamHandler($_ENV['LOGFILE'])]);
                }
            }

            $this->log->info('testing '. $this->getName());
            $this->stream = new Pool($servers, null, $this->log);
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
