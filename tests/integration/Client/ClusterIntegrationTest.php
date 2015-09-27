<?php

namespace Phloppy\Client;

use Phloppy\Exception\CommandException;

class ClusterIntegrationTest extends AbstractIntegrationTest {

    public function testMeet()
    {
        $cluster = new Cluster($this->stream);
        $ok = $cluster->meet([$this->stream->getNodeUrl()]);
    }
}
