
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

use Fabiang\Xmpp\Options;
use Fabiang\Xmpp\Client;
use Fabiang\Xmpp\Protocol\Roster;
use Fabiang\Xmpp\Protocol\Presence;
use Fabiang\Xmpp\Protocol\Invitation;
use Fabiang\Xmpp\Protocol\Message;
use Fabiang\Xmpp\Protocol\GroupChatConfig;
use Fabiang\Xmpp\Protocol\GroupChatCreate;
use Fabiang\Xmpp\Protocol\ProtocolImplementationInterface;
use Fabiang\Xmpp\Util\XML;

class TikiXmppChat
{
    public $client;

    public function __construct($params = [])
    {
        $default = [
            "scheme" => "tcp",
            "host" => "",
            "port" => 5222,
            "user" => "",
            "pass" => "",
        ];
        $params = array_merge($default, $params);
        $address = "{$params['scheme']}://{$params['host']}:{$params['port']}";

        $options = new Options($address);
        $options->setUsername($params['user']);
        $options->setPassword($params['pass']);

        $options->setContextOptions(
            [
                'ssl' => ['verify_peer' => false]
            ]
        );

        $this->setClient(new Client($options));
    }

    public function connect()
    {
        $conn = $this->getConnection();
        $conn->connect();
        return $this;
        $conn->getSocket()->setBlocking(false);
    }

    public function disconnect()
    {
        $this->getClient()->getConnection()->disconnect();
        return $this;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function setClient($client)
    {
        $this->client = $client;
        return $this;
    }

    public function getConnection()
    {
        return $this->getClient()->getConnection();
    }

    public function getJid()
    {
        return $this->getClient()->getOptions()->getJid();
    }

    public function getResource()
    {
        $jid = $this->getJid();
        return substr($jid, 1 + strrpos($jid, '/'));
    }

    public function getUsername()
    {
        return $this->getClient()->getOptions()->getUsername();
    }

    /** User actions */
    public function sendInvitation($room, $guest)
    {
        $this->getClient()->send(new Invitation($this->getJid(), $room, $guest));
        return $this;
    }

    public function sendMessage($message, $to, $type = Message::TYPE_CHAT)
    {
        $this->getClient()->send(new Message($message, $to, $type));
        return $this;
    }

    public function sendPresence($priority = 1, $to = null, $nickname = null)
    {
        $this->getClient()->send(new Presence($priority, $to, $nickname));
        return $this;
    }

    public function createRoom($owner, $room, $attrs = [])
    {
        $conn = $this->getConnection();
        $this->getClient()->send(new GroupChatCreate($owner, $room));
        $this->getClient()->send(new GroupChatConfig($owner, $room, $attrs));

        $attempts = 5;
        $dom = null;
        while ($attempts > 0 || $dom) {
            $dom = $conn->receive();

            if ($dom) {
                $xpath = new DOMXpath($dom);
                $x = $xpath->query('//x');
                $x = $x->length > 0 ? $x[0] : null;
                $test = ! is_null($x);
                $test = $test && $x->parentNode->nodeName === 'presence';
                $test = $test && $x->parentNode->getAttribute('from') === $room;
                $test = $test && $x->parentNode->getAttribute('to') === $owner;

                // probably our response
                if ($test) {
                    $test = $xpath->query('status[@code=201]', $x)->length > 0
                        && $xpath->query('status[@code=110]', $x)->length > 0;
                    return $test;
                }
            } else {
                $attempts -= 1;
                sleep(1);
            }
        }

        return false;
    }
}