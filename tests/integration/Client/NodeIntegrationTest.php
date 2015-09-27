<?php

namespace Phloppy\Client;

use Phloppy\Exception\CommandException;
use Phloppy\Job;

class NodeIntegrationTest extends AbstractIntegrationTest {

    public function testAuth()
    {
        $server = new Node($this->stream);

        try {
            $ok = $server->auth('test');
            $this->assertNotNull($ok);
        } catch (CommandException $e) {
            $this->assertEquals('ERR Client sent AUTH, but no password is set', $e->getMessage());
        }
    }

    public function testHello()
    {
        $server = new Node($this->stream);
        $nodes  = $server->hello();

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
        $server = new Node($this->stream);
        $this->assertTrue($server->ping());
    }


    public function testInfo()
    {
        $server = new Node($this->stream);
        $info   = $server->info();

        $this->assertArrayHasKey('Server', $info);
        $this->assertArrayHasKey('Clients', $info);
        $this->assertArrayHasKey('Memory', $info);
        $this->assertArrayHasKey('Jobs', $info);
        $this->assertArrayHasKey('Queues', $info);
        $this->assertArrayHasKey('Persistence', $info);
        $this->assertArrayHasKey('Stats', $info);
    }

    public function testDel()
    {
        $node = new Node($this->stream);
        $this->assertEquals(0, $node->del(['foo']));
    }

    public function testJScan()
    {
        $p     = new Producer($this->stream);
        $queue = uniqid('jscan_');
        $n     = rand(0, 100);

        for ($i = 0; $i < $n; $i++) {
            $p->addJob($queue, Job::create(['body' => '23']));
        }

        $node  = new Node($this->stream, $this->log);
        $it    = $node->jscan(5, [$queue], [Job::STATE_QUEUED]);
        $busy  = $node->jscan(0, [$queue], [Job::STATE_QUEUED]);

        $iterCount = $busyCount = 0;
        $jobs   = [];

        foreach($it as $k => $job) {
            $iterCount++;
            $jobs[] = $job;
        }

        foreach ($busy as $k => $job) {
            $busyCount++;
        }

        // cleanup
        $this->assertEquals($n, $node->del($jobs));
        $this->assertEquals($n, $iterCount);
    }
}
