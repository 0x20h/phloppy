<?php
namespace Disque;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

use Disque\Exception\CommandException;

class Client {

    /**
     * @var Stream
     */
    protected $stream;

    /**
     * @var LoggerInterface
     */
    protected $log;

    /**
     * @param array $servers Array of server info (e.g. tcp://128.0.01:4444/)
     * @param LoggerInterface $log
     */
    public function __construct(Stream $stream, LoggerInterface $log = null)
    {
        if (!$log) {
            $log = new NullLogger();
        }

        $this->log = $log;
        $this->stream = $stream;
    }

    /**
     * Authenticate against disque.
     *
     * @param $password
     * @return boolean true if authenticated. False otherwise.
     *
     * @throws CommandException
     */
    public function auth($password)
    {
        $rsp = $this->send(["AUTH", $password]);
        return $rsp === 'OK';
    }


    /**
     * Send a ping command to the connected server.
     *
     * @return bool True if the ping is acknowleged. False otherwise.
     */
    public function ping()
    {
        try {
            return $this->send(['PING']) === 'PONG';
        } catch(\Exception $e) {
            $this->log->emergency($e->getMessage());
            return false;
        }
    }


    /**
     * @return array
     */
    public function info()
    {
        $rsp = $this->send(['INFO']);
        $info = [];

        $sections = preg_split('/^#/m', $rsp);
        foreach ($sections as $section) {
            $lines = explode("\r\n", trim($section));
            $header = trim($lines[0]);
            array_shift($lines);
            $lines = array_reduce($lines, function($c, $e) {
                list($k,$v) = explode(':', $e);
                $c[$k] = $v;
                return $c;
            }, []);

            $info[$header] = $lines;
        }

        return $info;
    }


    /**
     * @return Node[]
     */
    public function hello()
    {
        $nodes = [];
        $rsp = $this->send(['HELLO']);
        $version = array_shift($rsp);
        switch($version) {
            case 1:
                $active = array_shift($rsp);
                $protocol = 'tcp';

                foreach($rsp as $node) {
                    $server = $protocol .'://'.$node[1].':'.$node[2];
                    $nodes[] = new Node($node[0], $server, $node[3]);
                }

                break;
        }

        return $nodes;
    }


    /**
     * Send request, retrieve response to the connected disque node.
     *
     * @param array $args
     * @return array|int|null|string
     *
     * @throws CommandException
     */
    protected function send(array $args = [])
    {
        return Resp::deserialize($this->stream->write(Resp::serialize($args)));
    }
}
