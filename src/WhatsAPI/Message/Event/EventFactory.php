<?php

namespace WhatsAPI\Message\Event;

use WhatsAPI\Exception\IncompleteMessageException;
use WhatsAPI\Protocol\BinTree\NodeReader;

class EventFactory
{

    public function createEvent(NodeReader $reader, $data)
    {
        $node = $reader->nextTree($data);
        while ($node != null) {
            $node = $this->reader->nextTree();
        }
    }

    /**
     * Process inbound data.
     *
     * @param string $data
     *                     The data to process.
     */
    protected function processInboundData($data)
    {
        try {
            $node = $this->reader->nextTree($data);
            while ($node != null) {
                $this->debugPrint($node->nodeString("rx  ") . "\n");
                if ($node->getTag() == "challenge") {
                    $this->processChallenge($node);
                } elseif ($node->getTag() == "success") {
                    $this->loginStatus = static::CONNECTED_STATUS;
                    $challengeData = $node->getData();
                    file_put_contents("nextChallenge.dat", $challengeData);
                    $this->writer->setKey($this->outputKey);
                } elseif ($node->getTag() == "failure") {
                    $this->eventManager()->fire("onLoginFailed", array(
                            $this->phoneNumber,
                            $node->getChild(0)->getTag()
                        ));
                }
                if ($node->getTag() == "message") {
                    array_push($this->messageQueue, $node);

                    //do not send received confirmation if sender is yourself
                    if (strpos($node->getAttribute('from'), $this->phoneNumber . '@' . static::WHATSAPP_SERVER) === false
                        &&
                        (
                            $node->hasChild("request")
                            ||
                            $node->hasChild("received")
                        )
                    ) {
                        $this->sendMessageReceived($node);
                    }

                    // check if it is a response to a status request
                    $foo = explode('@', $node->getAttribute('from'));
                    if (is_array($foo) && count($foo) > 1 && strcmp($foo[1], "s.us") == 0 && $node->getChild('body') != null) {
                        $this->eventManager()->fire('onGetStatus', array(
                                $this->phoneNumber,
                                $node->getAttribute('from'),
                                $node->getAttribute('type'),
                                $node->getAttribute('id'),
                                $node->getAttribute('t'),
                                $node->getChild("body")->getData()
                            ));
                    }
                    if ($node->hasChild('x') && $this->lastId == $node->getAttribute('id')) {
                        $this->sendNextMessage();
                    }
                    if ($this->newMsgBind && $node->getChild('body')) {
                        $this->newMsgBind->process($node);
                    }
                    if ($node->getChild('composing') != null) {
                        $this->eventManager()->fire('onMessageComposing', array(
                                $this->phoneNumber,
                                $node->getAttribute('from'),
                                $node->getAttribute('id'),
                                $node->getAttribute('type'),
                                $node->getAttribute('t')
                            ));
                    }
                    if ($node->getChild('paused') != null) {
                        $this->eventManager()->fire('onMessagePaused', array(
                                $this->phoneNumber,
                                $node->getAttribute('from'),
                                $node->getAttribute('type'),
                                $node->getAttribute('id'),
                                $node->getAttribute('t')
                            ));
                    }
                    if ($node->getChild('notify') != null && $node->getChild(0)->getAttribute('name') != '' && $node->getChild('body') != null) {
                        $author = $node->getAttribute("author");
                        if ($author == "") {
                            //private chat message
                            $this->eventManager()->fire('onGetMessage', array(
                                    $this->phoneNumber,
                                    $node->getAttribute('from'),
                                    $node->getAttribute('id'),
                                    $node->getAttribute('type'),
                                    $node->getAttribute('t'),
                                    $node->getChild("notify")->getAttribute('name'),
                                    $node->getChild("body")->getData()
                                ));
                        } else {
                            //group chat message
                            $this->eventManager()->fire('onGetGroupMessage', array(
                                    $this->phoneNumber,
                                    $node->getAttribute('from'),
                                    $author,
                                    $node->getAttribute('id'),
                                    $node->getAttribute('type'),
                                    $node->getAttribute('t'),
                                    $node->getChild("notify")->getAttribute('name'),
                                    $node->getChild("body")->getData()
                                ));
                        }
                    }
                    if ($node->hasChild('notification') && $node->getChild('notification')->getAttribute('type') == 'picture') {
                        if ($node->getChild('notification')->hasChild('set')) {
                            $this->eventManager()->fire('onProfilePictureChanged', array(
                                    $this->phoneNumber,
                                    $node->getAttribute('from'),
                                    $node->getAttribute('id'),
                                    $node->getAttribute('t')
                                ));
                        } elseif ($node->getChild('notification')->hasChild('delete')) {
                            $this->eventManager()->fire('onProfilePictureDeleted', array(
                                    $this->phoneNumber,
                                    $node->getAttribute('from'),
                                    $node->getAttribute('id'),
                                    $node->getAttribute('t')
                                ));
                        }
                    }
                    if ($node->getChild('notify') != null && $node->getChild(0)->getAttribute('name') != null && $node->getChild('media') != null) {
                        if ($node->getChild(2)->getAttribute('type') == 'image') {
                            $this->eventManager()->fire('onGetImage', array(
                                    $this->phoneNumber,
                                    $node->getAttribute('from'),
                                    $node->getAttribute('id'),
                                    $node->getAttribute('type'),
                                    $node->getAttribute('t'),
                                    $node->getChild(0)->getAttribute('name'),
                                    $node->getChild(2)->getAttribute('size'),
                                    $node->getChild(2)->getAttribute('url'),
                                    $node->getChild(2)->getAttribute('file'),
                                    $node->getChild(2)->getAttribute('mimetype'),
                                    $node->getChild(2)->getAttribute('filehash'),
                                    $node->getChild(2)->getAttribute('width'),
                                    $node->getChild(2)->getAttribute('height'),
                                    $node->getChild(2)->getData()
                                ));
                        } elseif ($node->getChild(2)->getAttribute('type') == 'video') {
                            $this->eventManager()->fire('onGetVideo', array(
                                    $this->phoneNumber,
                                    $node->getAttribute('from'),
                                    $node->getAttribute('id'),
                                    $node->getAttribute('type'),
                                    $node->getAttribute('t'),
                                    $node->getChild(0)->getAttribute('name'),
                                    $node->getChild(2)->getAttribute('url'),
                                    $node->getChild(2)->getAttribute('file'),
                                    $node->getChild(2)->getAttribute('size'),
                                    $node->getChild(2)->getAttribute('mimetype'),
                                    $node->getChild(2)->getAttribute('filehash'),
                                    $node->getChild(2)->getAttribute('duration'),
                                    $node->getChild(2)->getAttribute('vcodec'),
                                    $node->getChild(2)->getAttribute('acodec'),
                                    $node->getChild(2)->getData()
                                ));
                        } elseif ($node->getChild(2)->getAttribute('type') == 'audio') {
                            $this->eventManager()->fire('onGetAudio', array(
                                    $this->phoneNumber,
                                    $node->getAttribute('from'),
                                    $node->getAttribute('id'),
                                    $node->getAttribute('type'),
                                    $node->getAttribute('t'),
                                    $node->getChild(0)->getAttribute('name'),
                                    $node->getChild(2)->getAttribute('size'),
                                    $node->getChild(2)->getAttribute('url'),
                                    $node->getChild(2)->getAttribute('file'),
                                    $node->getChild(2)->getAttribute('mimetype'),
                                    $node->getChild(2)->getAttribute('filehash'),
                                    $node->getChild(2)->getAttribute('duration'),
                                    $node->getChild(2)->getAttribute('acodec'),
                                ));
                        } elseif ($node->getChild(2)->getAttribute('type') == 'vcard') {
                            $this->eventManager()->fire('onGetvCard', array(
                                    $this->phoneNumber,
                                    $node->getAttribute('from'),
                                    $node->getAttribute('id'),
                                    $node->getAttribute('type'),
                                    $node->getAttribute('t'),
                                    $node->getChild(0)->getAttribute('name'),
                                    $node->getChild(2)->getChild(0)->getAttribute('name'),
                                    $node->getChild(2)->getChild(0)->getData()
                                ));
                        } elseif ($node->getChild(2)->getAttribute('type') == 'location') {
                            $url = $node->getChild(2)->getAttribute('url');
                            $name = $node->getChild(2)->getAttribute('name');

                            $this->eventManager()->fire('onGetLocation', array(
                                    $this->phoneNumber,
                                    $node->getAttribute('from'),
                                    $node->getAttribute('id'),
                                    $node->getAttribute('type'),
                                    $node->getAttribute('t'),
                                    $node->getChild(0)->getAttribute('name'),
                                    $name,
                                    $node->getChild(2)->getAttribute('longitude'),
                                    $node->getChild(2)->getAttribute('latitude'),
                                    $url,
                                    $node->getChild(2)->getData()
                                ));
                        }
                    }
                    if ($node->getChild('x') != null) {
                        $this->serverReceivedId = $node->getAttribute('id');
                        $this->eventManager()->fire('onMessageReceivedServer', array(
                                $this->phoneNumber,
                                $node->getAttribute('from'),
                                $node->getAttribute('id'),
                                $node->getAttribute('type'),
                                $node->getAttribute('t')
                            ));
                    }
                    if ($node->getChild('received') != null) {
                        $this->eventManager()->fire('onMessageReceivedClient', array(
                                $this->phoneNumber,
                                $node->getAttribute('from'),
                                $node->getAttribute('id'),
                                $node->getAttribute('type'),
                                $node->getAttribute('t')
                            ));
                    }
                    if ($node->getAttribute('type') == "subject") {
                        print_r($node);
                        $this->eventManager()->fire('onGetGroupsSubject', array(
                                $this->phoneNumber,
                                reset(explode('@', $node->getAttribute('from'))),
                                $node->getAttribute('t'),
                                reset(explode('@',$node->getAttribute('author'))),
                                $node->getChild(0)->getAttribute('name'),
                                $node->getChild(2)->getData()
                            ));
                    }
                }
                if ($node->getTag() == "presence" && $node->getAttribute("status") == "dirty") {
                    //clear dirty
                    $categories = array();
                    if (count($node->getChildren()) > 0)
                        foreach ($node->getChildren() as $child) {
                            if ($child->getTag() == "category") {
                                $categories[] = $child->getAttribute("name");
                            }
                        }
                    $this->sendClearDirty($categories);
                }
                if (strcmp($node->getTag(), "presence") == 0
                    && strncmp($node->getAttribute('from'), $this->phoneNumber, strlen($this->phoneNumber)) != 0
                    && strpos($node->getAttribute('from'), "-") == false
                    && $node->getAttribute('type') != null) {
                    $this->eventManager()->fire('onPresence', array(
                            $this->phoneNumber,
                            $node->getAttribute('from'),
                            $node->getAttribute('type')
                        ));
                }
                if ($node->getTag() == "presence"
                    && strncmp($node->getAttribute('from'), $this->phoneNumber, strlen($this->phoneNumber)) != 0
                    && strpos($node->getAttribute('from'), "-") !== false
                    && $node->getAttribute('type') != null) {
                    $groupId = reset(explode('@', $node->getAttribute('from')));
                    if ($node->getAttribute('add') != null) {
                        $this->eventManager()->fire('onGroupsParticipantsAdd', array(
                                $this->phoneNumber,
                                $groupId, reset(explode('@', $node->getAttribute('add')))
                            ));
                    } elseif ($node->getAttribute('remove') != null) {
                        $this->eventManager()->fire('onGroupsParticipantsRemove', array(
                                $this->phoneNumber,
                                $groupId,
                                reset(explode('@', $node->getAttribute('remove'))),
                                reset(explode('@', $node->getAttribute('author')))
                            ));
                    }
                }
                if ($node->getTag() == "iq"
                    && $node->getAttribute('type') == "get"
                    && $node->getChild(0)->getTag() == "ping") {
                    $this->eventManager()->fire('onPing', array(
                            $this->phoneNumber,
                            $node->getAttribute('id')
                        )
                    );
                    $this->sendPong($node->getAttribute('id'));
                }
                if ($node->getTag() == "iq"
                    && $node->getAttribute('type') == "result") {
                    $this->serverReceivedId = $node->getAttribute('id');
                    if ($node->getChild(0) != null &&
                        $node->getChild(0)->getTag() == "query") {
                        if ($node->getChild(0)->getAttribute('xmlns') == 'jabber:iq:privacy') {
                            $this->eventManager()->fire("onGetPrivacyBlockedList", array(
                                    $this->phoneNumber,
                                    $node->getChild(0)->getChild(0)->getChildren()
                                )
                            );
                        }
                        if ($node->getChild(0)->getAttribute('xmlns') == 'jabber:iq:last') {
                            $this->eventManager()->fire("onGetRequestLastSeen", array(
                                    $this->phoneNumber,
                                    $node->getAttribute('from'),
                                    $node->getAttribute('id'),
                                    $node->getChild(0)->getAttribute('seconds')
                                )
                            );
                        }
                        array_push($this->messageQueue, $node);
                    }
                    if ($node->getChild(0) != null && $node->getChild(0)->getTag() == "props") {
                        //server properties
                        $props = array();
                        foreach ($node->getChild(0)->getChildren() as $child) {
                            $props[$child->getAttribute("name")] = $child->getAttribute("value");
                        }
                        $this->eventManager()->fire("onGetServerProperties", array(
                                $this->phoneNumber,
                                $node->getChild(0)->getAttribute("version"),
                                $props
                            )
                        );
                    }
                    if ($node->getChild(0) != null && $node->getChild(0)->getTag() == "picture") {
                        $this->eventManager()->fire("onGetProfilePicture", array(
                                $this->phoneNumber,
                                $node->getAttribute("from"),
                                $node->getChild("picture")->getAttribute("type"),
                                $node->getChild("picture")->getData()
                            ));
                    }
                    if ($node->getChild(0) != null && $node->getChild(0)->getTag() == "media") {
                        $this->processUploadResponse($node);
                    }
                    if ($node->getChild(0) != null && $node->getChild(0)->getTag() == "duplicate") {
                        $this->processUploadResponse($node);
                    }
                    if ($node->getAttribute('id') == 'group') {
                        //There are multiple types of Group reponses. Also a valid group response can have NO children.
                        //Events fired depend on text in the ID field.
                        $groupList = array();
                        if ($node->getChild(0) != null) {
                            foreach ($node->getChildren() as $child) {
                                $groupList[] = $child->getAttributes();
                            }
                        }
                        if ($node->getAttribute('id') == 'creategroup') {
                            $this->groupId = $node->getChild(0)->getAttribute('id');
                            $this->eventManager()->fire('onGroupsChatCreate', array(
                                    $this->phoneNumber,
                                    $this->groupId
                                ));
                        }
                        if ($node->getAttribute('id') == 'endgroup') {
                            $this->groupId = $node->getChild(0)->getChild(0)->getAttribute('id');
                            $this->eventManager()->fire('onGroupsChatEnd', array(
                                    $this->phoneNumber,
                                    $this->groupId
                                ));
                        }
                        if ($node->getAttribute('id') == 'getgroups') {
                            $this->eventManager()->fire('onGetGroups', array(
                                    $this->phoneNumber,
                                    $groupList
                                ));
                        }
                        if ($node->getAttribute('id') == 'getgroupinfo') {
                            $this->eventManager()->fire('onGetGroupsInfo', array(
                                    $this->phoneNumber,
                                    $groupList
                                ));
                        }
                        if ($node->getAttribute('id') == 'getgroupparticipants') {
                            $groupId = reset(explode('@', $node->getAttribute('from')));
                            $this->eventManager()->fire('onGetGroupParticipants', array(
                                    $this->phoneNumber,
                                    $groupId,
                                    $groupList
                                ));
                        }

                    }
                }
                if ($node->getTag() == "iq" && $node->getAttribute('type') == "error") {
                    $this->serverReceivedId = $node->getAttribute('id');
                    $this->eventManager()->fire('onGetError', array(
                            $this->phoneNumber,
                            $node->getChild(0)
                        ));
                }
                $node = $this->reader->nextTree();
            }
        } catch (IncompleteMessageException $e) {
            $this->incompleteMessage = $e->getInput();
        }
    }

}
