<?php

namespace WhatsAPI\Message\Event;

use WhatsAPI\Client\Client;
use WhatsAPI\Message\Node\NodeInterface;

class NodeEvent extends AbstractEvent
{
    /**
     * @var NodeInterface
     */
    protected $node;

    /**
     * @param Client        $client
     * @param NodeInterface $node
     */
    public function __construct(Client $client, NodeInterface $node)
    {
        $this->setClient($client);
        $this->setTarget($client);
        $this->setNode($node);
        $this->setName('received.node.' . $node->getName());
    }

    /**
     * @param  NodeInterface $node
     * @return $this
     */
    public function setNode(NodeInterface $node)
    {
        $this->node = $node;

        return $this;
    }

    /**
     * @return NodeInterface
     */
    public function getNode()
    {
        return $this->node;
    }
}
