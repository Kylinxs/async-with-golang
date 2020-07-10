
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\SabreDav;

use Sabre\CardDAV;
use Sabre\DAV;
use TikiLib;
use Perms;

/**
 * Tiki database CardDAV backend.
 *
 * This backend is used to store address book data in Tiki MySQL database.
 * It allows provides support for 2 system-level address books:
 * 1. webmail contacts
 * 2. user system data
 */
class CardDAVBackend extends CardDAV\Backend\AbstractBackend
{
    /**
     * Returns the list of addressbooks for a specific user.
     *
     * @param string $principalUri
     *
     * @return array
     */
    public function getAddressBooksForUser($principalUri)
    {
        global $user;

        $principal = PrincipalBackend::mapUriToUser($principalUri);

        if ($principal != $user) {
            throw new DAV\Exception\Forbidden("You don't have permission to view this user's address books.");
        }

        $result = [];

        $addressBookTypes = AddressBookType\Factory::all($user);

        foreach ($addressBookTypes as $abt) {
            if (! $abt->isEnabled()) {
                continue;
            }
            foreach ($abt->getAddressBooks() as $ab) {
                $result[] = $ab;
            }
        }

        return $result;
    }

    /**
     * Updates properties for an address book.
     *
     * The list of mutations is stored in a Sabre\DAV\PropPatch object.
     * To do the actual updates, you must tell this object which properties
     * you're going to process with the handle() method.
     *
     * Calling the handle method is like telling the PropPatch object "I
     * promise I can handle updating this property".
     *
     * Read the PropPatch documentation for more info and examples.
     *
     * @param string               $addressBookId
     * @param \Sabre\DAV\PropPatch $propPatch
     */
    public function updateAddressBook($addressBookId, \Sabre\DAV\PropPatch $propPatch)
    {
        global $user;
        $addressBook = AddressBookType\Factory::fromId($addressBookId, $user);

        if (! $addressBook instanceof AddressBookType\Custom) {
            throw new DAV\Exception\Forbidden("Address book properties are read-only.");
        }

        $supportedProperties = [
            '{DAV:}displayname',
            '{' . CardDAV\Plugin::NS_CARDDAV . '}addressbook-description',
        ];

        $propPatch->handle($supportedProperties, function ($mutations) use ($addressBookId) {
            $updates = [];
            foreach ($mutations as $property => $newValue) {
                switch ($property) {
                    case '{DAV:}displayname':
                        $updates['name'] = $newValue;
                        break;
                    case '{' . CardDAV\Plugin::NS_CARDDAV . '}addressbook-description':
                        $updates['description'] = $newValue;
                        break;
                }
            }
            TikiLib::lib('addressbook')->update_address_book($addressBookId, $updates);
            return true;
        });
    }

    /**
     * Creates a new address book.
     *
     * @param string $principalUri
     * @param string $url          just the 'basename' of the url
     * @param array  $properties
     *
     * @return int Last insert id
     */
    public function createAddressBook($principalUri, $url, array $properties)
    {
        global $user;

        $principal = PrincipalBackend::mapUriToUser($principalUri);

        if ($principal != $user) {
            throw new DAV\Exception\Forbidden("You don't have permission to create address books for this user.");
        }

        $values = [
            'name' => null,
            'description' => null,
            'user' => $user,
            'uri' => $url,
        ];

        foreach ($properties as $property => $newValue) {
            switch ($property) {
                case '{DAV:}displayname':
                    $values['name'] = $newValue;
                    break;
                case '{' . CardDAV\Plugin::NS_CARDDAV . '}addressbook-description':
                    $values['description'] = $newValue;
                    break;
                default:
                    throw new DAV\Exception\BadRequest('Unknown property: ' . $property);
            }
        }

        return TikiLib::lib('addressbook')->update_address_book(0, $values);
    }

    /**
     * Deletes an entire addressbook and all its contents.
     *
     * @param int $addressBookId
     */
    public function deleteAddressBook($addressBookId)
    {
        global $user;
        $addressBook = AddressBookType\Factory::fromId($addressBookId, $user);

        if (! $addressBook instanceof AddressBookType\Custom) {
            throw new DAV\Exception\Forbidden("Address book cannot be deleted.");
        }

        TikiLib::lib('addressbook')->delete_address_book($addressBookId);
    }

    /**
     * Returns all cards for a specific addressbook id.
     *
     * This method should return the following properties for each card:
     *   * carddata - raw vcard data
     *   * uri - Some unique url
     *   * lastmodified - A unix timestamp
     *
     * It's recommended to also return the following properties:
     *   * etag - A unique etag. This must change every time the card changes.
     *   * size - The size of the card in bytes.
     *
     * If these last two properties are provided, less time will be spent
     * calculating them. If they are specified, you can also ommit carddata.
     * This may speed up certain requests, especially with large cards.
     *
     * @param mixed $addressBookId
     *
     * @return array
     */
    public function getCards($addressBookId)
    {
        global $user;
        $addressBook = AddressBookType\Factory::fromId($addressBookId, $user);
        return $addressBook->getCards();
    }

    /**
     * Returns a specific card.
     *
     * The same set of properties must be returned as with getCards. The only
     * exception is that 'carddata' is absolutely required.
     *
     * If the card does not exist, you must return false.
     *
     * @param mixed  $addressBookId
     * @param string $cardUri
     *
     * @return array
     */
    public function getCard($addressBookId, $cardUri)
    {
        return array_shift($this->getMultipleCards($addressBookId, [$cardUri]));
    }

    /**
     * Returns a list of cards.
     *
     * This method should work identical to getCard, but instead return all the
     * cards in the list as an array.
     *
     * If the backend supports this, it may allow for some speed-ups.
     *
     * @param mixed $addressBookId
     * @param array $uris
     *
     * @return array
     */
    public function getMultipleCards($addressBookId, array $uris)
    {
        global $user;
        $addressBook = AddressBookType\Factory::fromId($addressBookId, $user);
        return $addressBook->getCards($uris);
    }

    /**
     * Creates a new card.
     *
     * The addressbook id will be passed as the first argument. This is the
     * same id as it is returned from the getAddressBooksForUser method.
     *
     * The cardUri is a base uri, and doesn't include the full path. The
     * cardData argument is the vcard body, and is passed as a string.
     *
     * It is possible to return an ETag from this method. This ETag is for the
     * newly created resource, and must be enclosed with double quotes (that
     * is, the string itself must contain the double quotes).
     *
     * You should only return the ETag if you store the carddata as-is. If a
     * subsequent GET request on the same card does not have the same body,
     * byte-by-byte and you did return an ETag here, clients tend to get
     * confused.
     *
     * If you don't return an ETag, you can just return null.
     *
     * @param mixed  $addressBookId
     * @param string $cardUri
     * @param string $cardData
     *
     * @return string|null
     */
    public function createCard($addressBookId, $cardUri, $cardData)
    {
        global $user;
        $addressBook = AddressBookType\Factory::fromId($addressBookId, $user);
        return $addressBook->createCard($cardUri, $cardData);
    }

    /**
     * Updates a card.
     *
     * The addressbook id will be passed as the first argument. This is the
     * same id as it is returned from the getAddressBooksForUser method.
     *
     * The cardUri is a base uri, and doesn't include the full path. The
     * cardData argument is the vcard body, and is passed as a string.
     *
     * It is possible to return an ETag from this method. This ETag should
     * match that of the updated resource, and must be enclosed with double
     * quotes (that is: the string itself must contain the actual quotes).
     *
     * You should only return the ETag if you store the carddata as-is. If a
     * subsequent GET request on the same card does not have the same body,
     * byte-by-byte and you did return an ETag here, clients tend to get
     * confused.
     *
     * If you don't return an ETag, you can just return null.
     *
     * @param mixed  $addressBookId
     * @param string $cardUri
     * @param string $cardData
     *
     * @return string|null
     */
    public function updateCard($addressBookId, $cardUri, $cardData)
    {
        global $user;
        $addressBook = AddressBookType\Factory::fromId($addressBookId, $user);
        return $addressBook->updateCard($cardUri, $cardData);
    }

    /**
     * Deletes a card.
     *
     * @param mixed  $addressBookId
     * @param string $cardUri
     *
     * @return bool
     */
    public function deleteCard($addressBookId, $cardUri)
    {
        global $user;
        $addressBook = AddressBookType\Factory::fromId($addressBookId, $user);
        return $addressBook->updateCard($cardUri, $cardData);
    }
}