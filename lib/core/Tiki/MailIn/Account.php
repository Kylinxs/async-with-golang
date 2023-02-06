<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\MailIn;

use TikiLib;
use TikiMail;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class Account
{
    private $source;
    private $actionFactory;

    private $accountAddress;
    private $anonymousAllowed;
    private $adminAllowed;
    private $sendResponses;
    private $discardAfter;
    private $defaultCategory;
    private $saveHtml;
    private $auto_attachments;
    private $inline_attachments;
    private $trackerId;
    private $preferences;

    private static function getSource(array $acc)
    {
        if ($acc['protocol'] == 'imap') {
            return new Source\Imap($acc['host'], $acc['port'], $acc['username'], $acc['pass']);
        } else {
            return new Source\Pop3($acc['host'], $acc['port'], $acc['username'], $acc['pass']);
        }
    }

    public function getPreferences()
    {
        return $this->preferences;
    }

    public function getTrackerId()
    {
        return $this->trackerId;
    }

    public static function test(array $acc)
    {
        $source = self::getSource($acc);
        return $source->test();
    }

    public static function fromDb(array $acc)
    {
        $account = new self();
        $account->source = self::getSource($acc);

        $wikiParams = [
            'namespace' => $acc['namespace'],
            'structure_routing' => $acc['routing'] == 'y',
        ];

        try {
            $container = \Tiki\TikiInit::getContainer();
            $type = str_replace('-', '', $acc['type']);
            $provider = $container->get("tiki.mailin.provider.{$type}");

            $account->actionFactory = $provider->getActionFactory($acc);
        } catch (ServiceNotFoundException $e) {
            throw new Exception\MailInException("Action factory not found.");
        }

        $account->accountAddress = $acc['account'];
        $account->anonymousAllowed = $acc['anonymous'] == 'y';
        $account->adminAllowed = $acc['admin'] == 'y';
        $account->sendResponses = $acc['respond_email'] == 'y';
        $account->discardAfter = $acc['discard_after'];
        $account->defaultCategory = $acc['categoryId'];
        $account->saveHtml = $acc['save_html'] == 'y';
        $account->deleteOnError = $acc['leave_email'] != 'y';
        $account->auto_attachments = $acc['attachments'] == 'y';
        $account->inline_attachments = $acc['show_inlineImages'] == 'y';
        $account->trackerId = $acc['trackerId'];
        $account->preferences = $acc['preferences'];

        return $account;
    }

    private function __construct()
    {
    }

    private function completeSuccess($message)
    {
        $message->delete();
    }

    private function completeFailure($message)
    {
        if ($this->deleteOnError) {
            $message->delete();
        }
    }

    public function isAnyoneAllowed()
    {
        return $this->anonymousAllowed;
    }

    private function canReceive(Source\Message $message)
    {
        $user = $message->getAssociatedUser();
        $perms = TikiLib::lib('tiki')->get_user_permission_accessor($user, null, null);

        if (! $user) {
            return $this->anonymousAllowed;
        } elseif ($perms->admin) {
            return $this->adminAllowed;
        } else {
            $userlib = TikiLib::lib('user');
            return $perms->send_mailin;
        }
    }

    private function getAction(Source\Message $message)
    {
        return $this->actionFactory->createAction($this, $message);
    }

    private function prepareMessage(Source\Message $message)
    {
        // TODO : This is rather primitive and implies we control the message source, need to make smarter

        if ($this->discardAfter) {
            $this->discard($message, $this->discardAfter);
        }

        $this->discard($message, '<div class="gmail_quote">');
        $this->discard($message, '<div class="gmail_extra">');
    }

    private function discard($message, $delimitor)
    {
        $body = $message->getBody();
        $pos = strpos($body, $delimitor);
        if ($pos !== false) {
            $body = substr($body, 0, $pos);
            $message->setBody($body);
        }

        $body = $message->getHtmlBody(false);
        $pos = strpos($body, $delimitor);
        if ($pos !== false) {
            $body = substr($body, 0, $pos);
            $message->setHtmlBody($body);
        }
    }

    public function sendFailureResponse(Source\Message $message, $condition)
    {
        global $prefs;
        $l = $prefs['language'];

        $mail = $this->getReplyMail($message);
        $pre = tra('Mail-in auto-reply', $l) . "\n\n";

        if ($condition == 'cant_use') {
            $mail->setText($pre . tra("Sorry, you can't use this feature.", $l));
        } elseif ($condition == 'disabled') {
            $mail->setText($pre . tra("The functionality you are trying to access is currently disabled.", $l));
        } elseif ($condition == 'permission_denied') {
            $mail->setText($pre . tra("Permission denied.", $l));
        } elseif ($condition == 'nothing_to_do') {
            $mail->setText($pre . tra("No required action found.", $l));
        }

        $this->sendFailureReply($message, $mail);
    }

    public function getReplyMail(Source\Message $message)
    {
        require_once 'lib/webmail/tikimaillib.php';
        $mail = new TikiMail();
        $mail->setFrom($this->accountAddress);
        $mail->setHeader('In-Reply-To', "<{$message->getMessageId()}>");
        $mail->setSubject("RE: {$message->getSubject()}");

        return $mail;
    }

    public function getAddress()
    {
        return $this->accountAddress;
    }

    public function sendFailureReply(Source\Me