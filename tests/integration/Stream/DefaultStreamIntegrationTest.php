<?php

namespace Phloppy\Stream;

use Phloppy\Exception\ConnectException;

class DefaultStreamIntegrationTest extends \PHPUnit_Framework_TestCase {

    /**
     * @expectedException \Phloppy\Exception\ConnectException
     */
    public function testConnectFails()
    {
        new DefaultStream('tcp://should.not.exist:42/');
    }


    public function testClose()
    {
        if (empty($_ENV['DISQUE_SERVERS'])) {
            $this->markTestSkipped('no disque servers configured');
        }

        try {
            $servers = explode(',', $_ENV['DISQUE_SERVERS']);
            $c = new DefaultStream($servers[0]);
        } catch(ConnectException $e) {
            $this->markTestSkipped($e->getMessage());
        }


        $this->assertTrue($c->isConnected());
        $c->close();
        $this->assertFalse($c->isConnected());
    }
}
