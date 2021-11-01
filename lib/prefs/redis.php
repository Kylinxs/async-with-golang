<?php

// (c) Copyright 2002-2019 by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

function prefs_redis_list()
{
    return [
        'redis_enabled' => [
            'name' => tra('Redis'),
            'description' => tra('Enable connection to Redis to store cache.'),
            'type' => 'flag',
            'hint' => tra('Requires PHP Redis.'),
            'extensions' => [ 'redis' ],
            'default' => 'n',
        ],
        'redis_host' => [
            'name' => tra('Redis Host'),
            'description' => tra('IP address or domain name for Redis server.'),
            'type' => 'text',
            'filter' => 'striptags',
            'default' => '',
            'extensions' => [ 'redis' ],
        ],
        'redis_port' => [
            'name' => tra('Redis Port'),
            'description' => tra('Port for Redis server.'),
            'type' => 'text',
            'size' => '4',
            'filter' => 'digits',
            'default' => '6379',
            'extensions' => [ 'redis' ],
        ],
        'redis_timeout' => [
            'name' => tra('Redis connection timeout'),
            'description' => tra('Seconds to wait before timeout when trying to connect to Redis. 0 means unlimited.'),
            'type' => 'text',
            'size' => 4,
            'filter' => 'digits',
            'units' => tra('seconds'),
            'default' => 3,
            'extensions' => [ 'redis' ],
        ],
        'redis_prefix' => [
            'name' => tra('Redis Prefix'),
            'description' => tra('When the Redis cluster is used by multiple applications, using unique prefixes for each of them helps avoid conflicts. Leave blank for none.'),
            'warning' => tra("Redis is single threaded, which means that when clearing cache which could take a while especially if the total Redis db is large, it could hold up the Redis server for other applications as well. Use separate instances instead of prefixing if that is a concern."),
            'filter' => 'word',
            'size' => 10,
            'type' => 'text',
            'default' => '',
            'extensions' => [ 'redis' ],
        ],
        'redis_expiry' => [
            'name' => tra('Redis cache expiry'),
            'description' => tra('Duration for which the cache will be kept. 0 means unlimited.'),
            'type' => 'text',
            'size' => 10,
            'filter' => 'digits',
            'units' => tra('seconds'),
            'default' => 0,
            'extensions' => [ 'redis' ],
        ],
    ];
}
