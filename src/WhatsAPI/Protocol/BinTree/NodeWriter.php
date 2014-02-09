<?php

namespace WhatsAPI\Protocol\BinTree;

use WhatsAPI\Protocol\Dictionary;
use WhatsAPI\Message\Node\NodeInterface;
use WhatsAPI\Protocol\KeyStream;

class NodeWriter
{
    private $output;
    private $tokenMap = array();
    /**
     * @var KeyStream
     */
    private $key;

    public function __construct(Dictionary $dictionary)
    {
        for ($i = 0; $i < count($dictionary); $i++) {
            if (strlen($dictionary[$i]) > 0) {
                $this->tokenMap[$dictionary[$i]] = $i;
            }
        }
    }

    /**
     * @return $this
     */
    public function resetKey()
    {
        $this->key = null;

        return $this;
    }

    /**
     * @param  KeyStream $key
     * @return $this
     */
    public function setKey(KeyStream $key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @param $domain
     * @param $resource
     * @return string
     */
    public function startStream($domain, $resource)
    {
        $attributes = array();
        $header = "WA";
        $header .= $this->writeInt8(1);
        $header .= $this->writeInt8(2);

        $attributes["to"] = $domain;
        $attributes["resource"] = $resource;
        $this->writeListStart(count($attributes) * 2 + 1);

        $this->output .= "\x01";
        $this->writeAttributes($attributes);
        $ret = $header . $this->flushBuffer();

        return $ret;
    }

    /**
     * @param  NodeInterface $node
     * @return string
     */
    public function write(NodeInterface $node)
    {
        if ($node == null) {
            $this->output .= "\x00";
        } else {
            $this->writeInternal($node);
        }

        return $this->flushBuffer();
    }

    /**
     * @param  NodeInterface $node
     * @return $this
     */
    protected function writeInternal(NodeInterface $node)
    {
        $len = 1;
        if ($node->getAttributes() != null) {
            $len += count($node->getAttributes()) * 2;
        }
        if (count($node->getChildren()) > 0) {
            $len += 1;
        }
        if (strlen($node->getData()) > 0) {
            $len += 1;
        }
        $this->writeListStart($len);
        $this->writeString($node->getName());
        $this->writeAttributes($node->getAttributes());
        if (strlen($node->getData()) > 0) {
            $this->writeBytes($node->getData());
        }
        if ($node->getChildren()) {
            $this->writeListStart(count($node->getChildren()));
            foreach ($node->getChildren() as $child) {
                $this->writeInternal($child);
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    protected function flushBuffer()
    {
        $data = (isset($this->key)) ? $this->key->encode($this->output, 0, strlen($this->output)) : $this->output;
        $size = strlen($data);
        $ret = $this->writeInt8(isset($this->key) ? (1 << 4) : 0);
        $ret .= $this->writeInt16($size);
        $ret .= $data;
        $this->output = "";

        return $ret;
    }

    /**
     * @param $token
     * @return $this
     */
    protected function writeToken($token)
    {
        if ($token < 0xf5) {
            $this->output .= chr($token);
        } elseif ($token <= 0x1f4) {
            $this->output .= "\xfe" . chr($token - 0xf5);
        }

        return $this;
    }

    /**
     * @param $user
     * @param $server
     * @return $this
     */
    protected function writeJid($user, $server)
    {
        $this->output .= "\xfa";
        if (strlen($user) > 0) {
            $this->writeString($user);
        } else {
            $this->writeToken(0);
        }
        $this->writeString($server);

        return $this;
    }

    /**
     * @param $v
     * @return string
     */
    protected function writeInt8($v)
    {
        $ret = chr($v & 0xff);

        return $ret;
    }

    /**
     * @param $v
     * @return string
     */
    protected function writeInt16($v)
    {
        $ret = chr(($v & 0xff00) >> 8);
        $ret .= chr(($v & 0x00ff) >> 0);

        return $ret;
    }

    /**
     * @param $v
     * @return string
     */
    protected function writeInt24($v)
    {
        $ret = chr(($v & 0xff0000) >> 16);
        $ret .= chr(($v & 0x00ff00) >> 8);
        $ret .= chr(($v & 0x0000ff) >> 0);

        return $ret;
    }

    /**
     * @param $bytes
     * @return $this
     */
    protected function writeBytes($bytes)
    {
        $len = strlen($bytes);
        if ($len >= 0x100) {
            $this->output .= "\xfd";
            $this->output .= $this->writeInt24($len);
        } else {
            $this->output .= "\xfc";
            $this->output .= $this->writeInt8($len);
        }
        $this->output .= $bytes;

        return $this;
    }

    /**
     * @param $tag
     * @return $this
     */
    protected function writeString($tag)
    {
        if (isset($this->tokenMap[$tag])) {
            $key = $this->tokenMap[$tag];
            $this->writeToken($key);
        } else {
            $index = strpos($tag, '@');
            if ($index) {
                $server = substr($tag, $index + 1);
                $user = substr($tag, 0, $index);
                $this->writeJid($user, $server);
            } else {
                $this->writeBytes($tag);
            }
        }

        return $this;
    }

    /**
     * @param $attributes
     * @return $this
     */
    protected function writeAttributes($attributes)
    {
        if ($attributes) {
            foreach ($attributes as $key => $value) {
                $this->writeString($key);
                $this->writeString($value);
            }
        }

        return $this;
    }

    /**
     * @param $len
     * @return $this
     */
    protected function writeListStart($len)
    {
        if ($len == 0) {
            $this->output .= "\x00";
        } elseif ($len < 256) {
            $this->output .= "\xf8" . chr($len);
        } else {
            $this->output .= "\xf9" . chr($len);
        }

        return $this;
    }
}
