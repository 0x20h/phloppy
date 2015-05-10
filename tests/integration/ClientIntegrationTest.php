<?php

namespace Phloppy;

use Phloppy\Exception\CommandException;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class ClientIntegrationTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var \Phloppy\Stream\Pool
     */
    private $link;

    protected function setUp()
    {
        if (empty($_ENV['DISQUE_SERVERS'])) {
            return $this->markTestSkipped('no disque servers configured');
        }

        $servers = explode(',', $_ENV['DISQUE_SERVERS']);
        $this->link = new Stream\Pool($servers);
    }


    public function testAuth()
    {
        $servers = explode(',', $_ENV['DISQUE_SERVERS']);
        $client = new Client($this->link);

        try {
            $ok = $client->auth('test');
            $this->assertNotNull($ok);
        } catch (CommandException $e) {
            $this->assertEquals('ERR Client sent AUTH, but no password is set', $e->getMessage());
        }
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

        $this->assertNotEmpty($allNodes);
    }


    public function testPing()
    {
        $client = new Client($this->link);
        $this->assertTrue($client->ping());
    }


    public function testInfo()
    {
        $client = new Client($this->link);
        $info = $client->info();
        $this->assertArrayHasKey('Server', $info);
        $this->assertArrayHasKey('Clients', $info);
        $this->assertArrayHasKey('Memory', $info);
        $this->assertArrayHasKey('Jobs', $info);
        $this->assertArrayHasKey('Queues', $info);
        $this->assertArrayHasKey('Persistence', $info);
        $this->assertArrayHasKey('Stats', $info);
    }
}
