<?php

namespace Phloppy\Statistic;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class JobOriginStatisticTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var LoggerInterface
     */
    private $log;


    protected function setUp()
    {
        // here for you, debugging
        //$this->log = new Logger(new StreamHandler('php://stdout'));
        $this->log = new NullLogger();
    }


    /**
     * @expectedException \InvalidArgumentException
     */
    public function testTooSmallAlpha()
    {
        new JobOriginStatistic(.4);
    }


    /**
     * @expectedException \InvalidArgumentException
     */
    public function testTooLargeAlpha()
    {
        new JobOriginStatistic(1.2);
    }


    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidAlpha()
    {
        new JobOriginStatistic('foo');
    }


    public function testNode()
    {
        $job = $this->getMockBuilder('Phloppy\Job')
            ->setConstructorArgs(['body'])
            ->getMock();

        $job->expects($this->any())->method('getOriginNode')->willReturn('a');
        $job->expects($this->any())->method('getQueue')->willReturn('aQueue');

        $statistics = new JobOriginStatistic(.8, $this->log);

        // lets assume that in 10 subsequent seconds one message is received per second
        $date = new \DateTime('now');

        for ($i = 0; $i < 50; $i++) {
            $statistics->update($job, $date);
            $date = $date->add(new \DateInterval('PT1S'));
        }

        // Statistics should be around 1. (per sec)
        $this->assertLessThan(.001, 1 - $statistics->node('aQueue', 'a'));
    }


    public function testNodes()
    {
        $jobA = $this->getMockBuilder('Phloppy\Job')
            ->setConstructorArgs(['body'])
            ->getMock();

        $jobB = clone $jobA;

        $jobA->expects($this->any())->method('getOriginNode')->willReturn('a');
        $jobA->expects($this->any())->method('getQueue')->willReturn('aQueue');

        $jobB->expects($this->any())->method('getOriginNode')->willReturn('b');
        $jobB->expects($this->any())->method('getQueue')->willReturn('aQueue');

        $statistics = new JobOriginStatistic(.8, $this->log);

        // lets assume that in 10 subsequent seconds one message is received per second
        $dateA = new \DateTime('now');
        // lets assume that in 20 subsequent seconds one message is received every second second
        $dateB = new \DateTime('now');

        for ($i = 0; $i < 45; $i++) {
            $statistics->update($jobA, $dateA);
            $statistics->update($jobB, $dateB);
            $dateA = $dateA->add(new \DateInterval('PT2S'));
            $dateB = $dateB->add(new \DateInterval('PT1S'));
        }

        $nodes = $statistics->nodes('aQueue');
        $expected = array('b', 'a');
        $this->assertEquals($expected, array_keys($nodes));
    }
}