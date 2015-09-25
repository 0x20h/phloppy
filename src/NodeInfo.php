<?php

namespace Phloppy;

class NodeInfo {

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $server;

    /**
     * @var int
     */
    private $priority;

    public function __construct($id, $server, $priority)
    {
        $this->id       = $id;
        $this->server   = $server;
        $this->priority = $priority;
    }

    /**
     * @return string
     */
    public function getServer()
    {
        return $this->server;
    }


    /**
     * @return string
     */
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
