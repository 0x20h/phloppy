<?php
namespace Phloppy\Client;

use Phloppy\Exception\CommandException;
use Phloppy\NodeInfo;

/**
 * Disque server commands.
 */
class Server extends AbstractClient {

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
                list($k,$v) = explode(':', $e);
                $c[$k]      = $v;

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
        $nodes   = [];
        $rsp     = $this->send(['HELLO']);
        $version = array_shift($rsp);

        switch($version) {
            case 1:
                /* $active = */ array_shift($rsp);
                $protocol = 'tcp';

                foreach($rsp as $node) {
                    $server  = $protocol .'://'. $node[1] .':'. $node[2];
                    $nodes[] = new NodeInfo($node[0], $server, $node[3]);
                }

                break;
        }

        return $nodes;
    }
}
