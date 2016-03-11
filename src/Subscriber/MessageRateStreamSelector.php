<?php

namespace Phloppy\Subscriber;

use Phloppy\Client\AbstractClient;
use Phloppy\Event\GetJobsEvent;
use Phloppy\Event\JobsReceivedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MessageRateStreamSelector implements EventSubscriberInterface
{
    public function __construct(AbstractClient $client)
    {
        $this->client = $client;
    }

    public static function getSubscribedEvents()
    {
        return [
            GetJobsEvent::ID => 'selectOptimalNode',
            JobsReceivedEvent::ID => 'updateNodeStats'
        ];
    }
}