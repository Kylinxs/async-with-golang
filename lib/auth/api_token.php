<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 * ApiToken library for access and modification of API tokens and OAuth tokens
 *
 * @uses TikiLib
 */
class ApiToken extends TikiLib
{
    private $table;

    public function __construct()
    {
        parent::__construct();
        $this->table = $this->table('tiki_api_tokens');
    }

    public function getTokens($conditions = [])
    {
        return $this->table->fetchAll([], $conditions, -1, -1, ['tokenId' => 'asc']);
    }

    public function getToken($tokenId)
    {
        if (is_numeric($tokenId)) {
            return $this->table->fetchFullRow(['tokenId' => (int) $tokenId]);
        } else {
            return $this->table->fetchFullRow(['token' => $tokenId]);
        }
    }

    public function createToken($token)
    {
        $this->table->deleteMultiple([
            'type' => $this->table->expr("$$ != 'manual'"),
            'expireAfter' => $this->table->expr("$$ < NOW()")
        ]);
        if (empty($token['token'])) {
            $token['token'] = $this->generate((string)$token['user'], microtime());
        }
        if ($this->getToken($token['token'])) {
            throw new ApiTokenException(tr('Access token already exists.'));
        }
        $token['created'] = $this->now;
        $token['lastModif'] = $this->now;
        $tokenId = $this->table->insert($token);
        return $this->getToken($tokenId);
    }

    public function updateToken($tokenId, $token)
    {
        $token['lastModif'] = $this->now;
        $this->table->update($token, ['tokenId' => $tokenId]);
        return $this->getToken($tokenId);
    }

    public function deleteToken($tokenId)
    {
        if (is_numeric($tokenId)) {
            return $this->table->delete(['tokenId' => $tokenId]);
        } else {
            return $this->table->delete(['token' => $tokenId]);
        }
    }

    public function validToken($token)
    {
        $token = $this->table->fetchFullRow(['token' => $token]);
        if (! $token) {
            return false;
        }
        if (! empty($token['expireAfter']) && $token['expireAfter'] < $this->now) {
            return false;
        }
        return $token;
    }

    public function hit($token)
    {
        $this->table->update(['hits' => $token['hits'] + 1], ['tokenId' => $token['tokenId']]);
    }

    private function generate($prefix = '', $suffix = '')
    {
        return hash('sha256', $prefix . uniqid() . $suffix);
    }
}

class ApiTokenException extends Exception
{
}
