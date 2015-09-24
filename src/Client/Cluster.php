<?php
namespace Phloppy\Client;

use Phloppy\Exception\CommandException;
use Phloppy\Node;

/**
 * Disque cluster commands.
 */
class Cluster extends AbstractClient {

    /**
     * @return Node[]
     */
    public function meet(array $nodes)
    {

        foreach ($nodes as $node) {
            list($host, $port) = explode(':', $node);
            $rsp               = $this->send(['CLUSTER', 'MEET', $host, (int) $port]);
            var_dump($rsp);
        }

        return $nodes;
    }
}
