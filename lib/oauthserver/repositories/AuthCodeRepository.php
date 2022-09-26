
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

include_once dirname(__DIR__) . '/entities/AuthCodeEntity.php';

use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;

class AuthCodeRepository implements AuthCodeRepositoryInterface
{
    public function getNewAuthCode()
    {
        return new AuthCodeEntity();
    }

    public function isAuthCodeRevoked($codeId)
    {
        return ! TikiLib::lib('api_token')->validToken($codeId);
    }

    public function persistNewAuthCode(AuthCodeEntityInterface $code)
    {
        try {
            $token = TikiLib::lib('api_token')->createToken([
                'type' => 'oauth_auth',
                'token' => $code->getIdentifier(),
                'label' => 'OAuth client ' . $code->getClient()->getIdentifier(),
                'user' => $code->getUserIdentifier(),
                'expireAfter' => $code->getExpiryDateTime()->getTimestamp(),
                'parameters' => json_encode([
                    'user'   => $code->getUserIdentifier(),
                    'client' => $code->getClient()->getIdentifier(),
                    'scopes' => $code->getScopes(),
                    'redirect' => $code->getRedirectUri(),
                ]),
            ]);
        } catch (ApiTokenException $e) {
            throw new UniqueTokenIdentifierConstraintViolationException($e->getMessage());
        }

        $code->setIdentifier($token['token']);
        return $code;
    }

    public function revokeAuthCode($codeId)
    {
        TikiLib::lib('api_token')->deleteToken($codeId);
        return $this;
    }
}