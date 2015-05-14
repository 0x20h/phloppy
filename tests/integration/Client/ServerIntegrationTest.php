<?php

namespace Phloppy\Client;

use Phloppy\Exception\CommandException;

class ServerIntegrationTest extends AbstractIntegrationTest {

    public function testAuth()
    {
        $server = new Server($this->stream);

        try {
            $ok = $server->auth('test');
            $this->assertNotNull($ok);
        } catch (CommandException $e) {
            $this->assertEquals('ERR Client sent AUTH, but no password is set', $e->getMessage());
        }
    }

    public function testHello()
    {
        $server = new Server($this->stream);
        $nodes = $server->hello();

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
        $server = new Server($this->stream);
        $this->assertTrue($server->ping());
    }


    public function testInfo()
    {
        $server = new Server($this->stream);
        $info = $server->info();
        $this->assertArrayHasKey('Server', $info);
        $this->assertArrayHasKey('Clients', $info);
        $this->assertArrayHasKey('Memory', $info);
        $this->assertArrayHasKey('Jobs', $info);
        $this->assertArrayHasKey('Queues', $info);
        $this->assertArrayHasKey('Persistence', $info);
        $this->assertArrayHasKey('Stats', $info);
    }

}
