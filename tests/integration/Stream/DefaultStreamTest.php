<?php

namespace Phloppy\Stream;

class DefaultStreamTest extends \PHPUnit_Framework_TestCase {

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

        $servers = explode(',', $_ENV['DISQUE_SERVERS']);
        $c = new DefaultStream($servers[0]);

        $this->assertTrue($c->isConnected());
        $c->close();
        $this->assertFalse($c->isConnected());
    }
}
