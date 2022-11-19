<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\Realtime;

use Exception;
use SplObjectStorage;
use Perms_Context;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

/**
 * Ratchet App reusing existing PHP session for connected clients.
 * Extend this class and call parent::onMessage() if you need to run it as sending user and related permissions.
 */
class SessionAwareApp implements MessageComponentInterface
{
    protected $clients;
    protected $sessions;

    public function __construct()
    {
        $this->clients = new SplObjectStorage();
        $this->sessions = new SplObjectStorage();
    }

    public function onOpen(ConnectionInterface $conn)
    {
        global $user;

        parse_str($conn->httpRequest->getUri()->getQuery(), $queryParameters);
        $this->sessions->attach($conn, $queryParameters['token']);

        // TODO: divide session switch and user retrival with a new preference
        $this->switchSession($conn);
        $this->clients->attach($conn, $user);
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $this->switchSession($from);
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        $this->sessions->detach($conn);
    }

    public function onError(ConnectionInterface $conn, Exception $e)
    {
        // TODO: log
        echo $e->getMessage();
        $conn->close();
    }

    protected function switchSession(ConnectionInterface $conn)
    {
        global $user, $tikilib, $prefs, $u_info, $smarty, $group, $default_group, $user_preferences;

        $session_id = $this->sessions[$conn];
        if (empty($session_id)) {
            return;
        }

        session_id($session_id);

        try {
            if (session_status() === PHP_SESSION_ACTIVE) {
                \Laminas\Session\Container::getDefaultManager()->destroy();
            }
            \Laminas\Session\Container::getDefaultManager()->start();
        } catch (Laminas\Session\Exception\ExceptionInterface $e) {
            // Ignore
        } catch (\Laminas\Stdlib\Exception\InvalidArgumentException $e) {
            // Ignore
        }


        $cookie_site = preg_replace("/[^a-zA-Z0-9]/", "", $prefs['cookie_name']);
        $user_cookie_site = 'tiki-user-' . $cookie_site;
        $user = $_SESSION['u_info']['login'];
        $_SESSION["$user_cookie_site"] = $user;

        $tikilib->setSessionId(session_id());

        require('lib/setup/user_prefs.php');
        $_permissionContext = new Perms_Context($user, false);
        $_permissionContext->activatePermanently();

        \Laminas\Session\Container::getDefaultManager()->writeClose();
    }
}
