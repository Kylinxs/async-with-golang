
<?php

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

/**
 * This class represents a Client Application that uses
 * Tiki as an Authorization Server.
 */
class ClientEntity implements ClientEntityInterface
{
    use EntityTrait;
    use ClientTrait;

    const TABLE = 'tiki_oauthserver_clients';

    public function __construct($data = [])
    {
        $data = array_merge([
            'id'    => 0,
            'name'          => '',
            'client_id'     => '',
            'client_secret' => '',
            'redirect_uri'  => '',
            'user'          => '',
        ], $data);

        $this->setId($data['id']);
        $this->setName($data['name']);
        $this->setClientId($data['client_id']);
        $this->setClientSecret($data['client_secret']);
        $this->setRedirectUri($data['redirect_uri']);
        $this->setUser($data['user']);
    }

    public static function build($data)
    {
        return new self($data);
    }

    public function setId($id)
    {
        $this->id = (int) $id;
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setIdentifier($client_id)
    {
        return $this->setClientId($client_id);
    }

    public function getIdentifier()
    {
        return $this->getClientId();
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setClientId($client_id)
    {
        $this->client_id = $client_id;
        return $this;
    }

    public function getClientId()
    {
        return $this->client_id;
    }

    public function setClientSecret($client_secret)
    {
        $this->client_secret = $client_secret;
        return $this;
    }

    public function getClientSecret()
    {
        return $this->client_secret;
    }

    public function setRedirectUri($redirect_uri)
    {
        $this->redirect_uri = $redirect_uri;
        return $this;
    }

    public function getRedirectUri()
    {
        return $this->redirect_uri;
    }

    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function toArray()
    {
        return [
            'id'            => $this->getId(),
            'name'          => $this->getName(),
            'client_id'     => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
            'redirect_uri'  => $this->getRedirectUri(),
            'user'          => $this->getUser(),
        ];
    }

    public function toJson()
    {
        return json_encode(
            $this->toArray(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
    }
}