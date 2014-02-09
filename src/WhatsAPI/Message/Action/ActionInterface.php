<?php

namespace WhatsAPI\Message\Action;

use WhatsAPI\Client\Client;

interface ActionInterface
{
    public function send(Client $client);
    /**
     * @return \WhatsAPI\Message\Node\NodeInterface
     */
    public function getNode();
}
