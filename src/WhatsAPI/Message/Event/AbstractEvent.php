<?php

namespace WhatsAPI\Message\Event;

use WhatsAPI\Client\Client;
use Zend\EventManager\Event;

abstract class AbstractEvent extends Event
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @param  Client $client
     * @return $this
     */
    public function setClient(Client $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }
}
