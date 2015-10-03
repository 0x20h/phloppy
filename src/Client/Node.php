<?php
namespace Phloppy\Client;

use Iterator;
use Phloppy\Client\Node\JScanIterator;
use Phloppy\Exception\CommandException;
use Phloppy\NodeInfo;

/**
 * Disque commands for local nodes.
 */
class Node extends AbstractClient {

    /**
     * Authenticate against a Disque node.
     *
     * @param $password
     * @return boolean true if authenticated. False otherwise.
     *
     * @throws CommandException
     */
    public function auth($password)
    {
        $rsp = $this->send(['AUTH', $password]);
        return $rsp === 'OK';
    }


    /**
     * Send a ping command to the connected server.
     *
     * @return boolean True if the ping is acknowleged. False otherwise.
     */
    public function ping()
    {
        return $this->send(['PING']) === 'PONG';
    }


    /**
     * @return array
     */
    public function info()
    {
        $rsp      = $this->send(['INFO']);
        $info     = [];
        $sections = preg_split('/^#/m', $rsp);

        foreach ($sections as $section) {
            $lines  = explode("\r\n", trim($section));
            $header = trim($lines[0]);

            array_shift($lines);

            $lines = array_reduce($lines, function($c, $e) {
                list($k, $v) = explode(':', $e);
                $c[$k]       = $v;

                return $c;
            }, []);

            $info[$header] = $lines;
        }

        return $info;
    }


    /**
     * @return NodeInfo[]
     */
    public function hello()
    {
        $nodes    = [];
        $response = $this->send(['HELLO']);
        $version  = array_shift($response);

        switch ($version) {
            case 1:
                // active node id
                array_shift($response);

                // in single node mode the server may not have
                // determinded its own IP address, so we just use the
                // node url that we used to connect.
                if (count($response === 1) && $response[0][1] === '') {
                    $host           = parse_url($this->stream->getNodeUrl(), PHP_URL_HOST);
                    $response[0][1] = $host;
                }

                foreach ($response as $node) {
                    $server  = 'tcp://'.$node[1].':'.$node[2];
                    $nodes[] = new NodeInfo($node[0], $server, $node[3]);
                }

                break;
        }

        return $nodes;
    }


    /**
     * DELETE jobs from the connected node.
     *
     * @param string[] $jobs Job IDs to delete.
     *
     * @return int Number of jobs deleted
     */
    public function del(array $jobs)
    {
        try {
            return (int) $this->send(array_merge(['DELJOB'], $jobs));
        } catch (CommandException $e) {
            return 0;
        }

    }


    /**
     * Retrieve an Iterator over available jobs on the connected Disque node.
     *
     * @param int        $count
     * @param array      $queues
     * @param array      $states
     * @param            $format
     *
     * @return Iterator
     * @see https://github.com/antirez/disque#jscan-cursor-count-count-busyloop-queue-queue-state-state1-state-state2--state-staten-reply-allid
     */
    public function jscan($count = 50, array $queues = [], array $states = [], $format = JScanIterator::FORMAT_ID)
    {
        $iterator = new JScanIterator($this->stream, $this->log);
        $iterator->setCount($count);
        $iterator->setQueues($queues);
        $iterator->setStates($states);
        $iterator->setFormat($format);

        return $iterator;
    }
}
