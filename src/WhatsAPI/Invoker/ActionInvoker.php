<?php

namespace WhatsAPI\Invoker;

use WhatsAPI\Client\Client;
use WhatsAPI\Message\Action\ActionInterface;

class ActionInvoker
{
    /**
     * @var int
     */
    protected $counter;
    /**
     * @var \WhatsAPI\Client\Client
     */
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Send the action
     *
     * @param ActionInterface $action
     */
    public function send(ActionInterface $action)
    {
        $action->send($this->client);
    }
}
