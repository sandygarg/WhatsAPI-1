<?php

namespace WhatsAPI\Message\Listener;

use WhatsAPI\Message\Event\NodeEvent;
use WhatsAPI\Message\Node\Challenge;
use WhatsAPI\Message\Node\Success;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;

class ReceivedNodeListener implements ListenerAggregateInterface
{
    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();

    /**
     * Attach one or more listeners
     *
     * Implementors may add an optional $priority argument; the EventManager
     * implementation will pass this to the aggregate.
     *
     * @param EventManagerInterface $events
     *
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $events->attach('received.node.challenge', array($this, 'challengeEvent'));
        $events->attach('received.node.success', array($this, 'successEvent'));
    }

    /**
     * Detach all previously attached listeners
     *
     * @param EventManagerInterface $events
     *
     * @return void
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $callback) {
            if ($events->detach($callback)) {
                unset($this->listeners[$index]);
            }
        }
    }

    public function challengeEvent(NodeEvent $e)
    {
        /** @var Challenge $node */
        $node = $e->getNode();
        $client = $e->getClient();
        $client->setChallengeData($node->getData());
    }

    public function successEvent(NodeEvent $e)
    {
        /** @var Success $node */
        $node = $e->getNode();
        $client = $e->getClient();

        $client->setConnected(true);
        $challengeData = $node->getData();
        file_put_contents($client->getChallengeDataFilepath(), $challengeData);
        $client->getNodeWriter()->setKey($client->getOutputKey());
    }
}
