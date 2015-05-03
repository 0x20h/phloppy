<?php

namespace Disque;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class ClientIntegrationTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var \Disque\Stream\Pool
     */
    private $link;

    protected function setUp()
    {
        $servers = explode(',', $_ENV['DISQUE_SERVERS']);
        $this->link = new Stream\Pool($servers);
    }

    public function testHello()
    {
        $servers = explode(',', $_ENV['DISQUE_SERVERS']);
        $client = new Client($this->link);
        $nodes = $client->hello();

        $this->assertNotEmpty($nodes);
        $allNodes = [];

        foreach($nodes as $node) {
            $allNodes[] = $node->getServer();
        }

        $this->assertNotEmpty(array_intersect($allNodes, $servers));
    }


    public function testPing()
    {
        $client = new Client($this->link);
        $this->assertTrue($client->ping());
    }
}
