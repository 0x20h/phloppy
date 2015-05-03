<?php

namespace Disque\Stream;

class PoolTest extends \PHPUnit_Framework_TestCase {

    public function testConnectFailsPartly()
    {
        $servers = explode(',', $_ENV['DISQUE_SERVERS']);
        $servers[] = 'tcp://totally.unknown.host:35594';

        for ($i = 0; $i < 100; $i++) {
            $pool = new Pool($servers);
            $this->assertTrue($pool->isConnected());
            $pool->close();
        }
    }

    /**
     * @expectedException \Disque\Exception\ConnectException
     * @expectedExceptionMessage unable to connect to any of [tcp://totally.unknown.host:35594]
     */
    public function testConnectFails()
    {
        $servers = ['tcp://totally.unknown.host:35594'];
        $pool = new Pool($servers);
        $this->assertTrue($pool->isConnected());
        $pool->close();
    }

    public function testReconnect()
    {
        $servers = explode(',', $_ENV['DISQUE_SERVERS']);
        $pool = new Pool($servers);
        $this->assertTrue($pool->isConnected());
        $connected = $pool->getActiveServer();
        $pool->reconnect();
        $this->assertTrue($pool->isConnected());
        $this->assertNotSame($connected, $pool->getActiveServer());
    }


    public function testGetServers()
    {
        $servers = explode(',', $_ENV['DISQUE_SERVERS']);
        $pool = new Pool($servers);
        $this->assertSame($servers, $pool->getServers());
    }

}
