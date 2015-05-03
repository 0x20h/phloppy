<?php

namespace Disque;

class Node {

    private $id;
    private $server;

    /**
     * @var int
     */
    private $priority;

    public function __construct($id, $server, $priority) {
        $this->id = $id;
        $this->server = $server;
        $this->priority = $priority;
    }

    public function getServer()
    {
        return $this->server;
    }


    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }
}
