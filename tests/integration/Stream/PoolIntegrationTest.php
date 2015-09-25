<?php

namespace Phloppy\Stream;

use Phloppy\Exception\ConnectException;

class PoolTest extends \PHPUnit_Framework_TestCase {

    public function testConnectFailsPartly()
    {
        $servers = explode(',', $_ENV['DISQUE_SERVERS']);

        try {
            new Pool($servers);
        } catch(ConnectException $e) {
            $this->markTestSkipped($e->getMessage());
        }

        $servers[] = 'tcp://127.0.2.2:35594';

        for ($i = 0; $i < 5; $i++) {
            $pool = new Pool($servers);

            // should always connect to one of the present servers and never fail
            $this->assertTrue($pool->isConnected());
            $pool->close();
        }
    }

    /**
     * @expectedException \Phloppy\Exception\ConnectException
     * @expectedExceptionMessage unable to connect to any of [tcp://127.0.2.2:35594]
     */
    public function testConnectFails()
    {
        $servers = ['tcp://127.0.2.2:35594'];
        $pool = new Pool($servers);
        $this->assertTrue($pool->isConnected());
        $pool->close();
    }

    public function testReconnect()
    {
        $servers = explode(',', $_ENV['DISQUE_SERVERS']);

        try {
            $pool = new Pool($servers);
        } catch(ConnectException $e) {
            $this->markTestSkipped($e->getMessage());
        }

        $this->assertTrue($pool->isConnected());
        $connected = $pool->getActiveServer();
        $pool->reconnect();
        $this->assertTrue($pool->isConnected());
        $this->assertNotSame($connected, $pool->getActiveServer());
    }


    public function testGetServers()
    {
        $servers = explode(',', $_ENV['DISQUE_SERVERS']);

        try {
            $pool = new Pool($servers);
        } catch(ConnectException $e) {
            $this->markTestSkipped($e->getMessage());
        }

        $this->assertSame($servers, $pool->getStreamUrls());
    }

}
