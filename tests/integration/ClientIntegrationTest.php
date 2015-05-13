<?php

namespace Phloppy;

use Phloppy\Exception\CommandException;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Phloppy\Exception\ConnectException;

class ClientIntegrationTest extends AbstractIntegrationTest {

    public function testAuth()
    {
        $servers = explode(',', $_ENV['DISQUE_SERVERS']);
        $client = new Client($this->stream);

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
        $client = new Client($this->stream);
        $nodes = $client->hello();

        $this->assertNotEmpty($nodes);
        $allNodes = [];

        foreach($nodes as $node) {
            $allNodes[] = $node->getServer();
            $this->assertNotEmpty($node->getId());
            $this->assertNotEmpty($node->getPriority());
            $this->assertNotEmpty($node->getServer());
        }

        $this->assertNotEmpty($allNodes);
    }


    public function testPing()
    {
        $client = new Client($this->stream);
        $this->assertTrue($client->ping());
    }


    public function testInfo()
    {
        $client = new Client($this->stream);
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
