<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 * Obtains the object permissions for each object. Bulk loading provides
 * loading for multiple objects in a single query.
 *
 * Parent parameter can be passed during initialization to configure
 * Factory to return parent object permissions. Currently supported parents are:
 * tracker item -> tracker
 * file -> file gallery
 * article -> topic
 * blog post -> blog
 * thread -> forum
 * event -> calendar
 */
class Perms_ResolverFactory_ObjectFactory implements Perms_ResolverFactory
{
    private $known = [];
    private $parent = '';

    public function __construct($parent = '')
    {
        $this->parent = $parent;
    }

    public function clear()
    {
        $this->known = [];
    }

    public function getHash(array $context)
    {
        if (isset($context['type'], $context['object'])) {
            // parent permissions should all go in one hash key, so they share the cache
            // they are essentially the same for all children
            if ($this->parent && isset($context['parentId']) && ($parentType = Perms::parentType($context['type']))) {
                return 'object:' . $parentType . ':' . $this->cleanObject($context['parentId']);
            } else {
                return 'object:' . $context['type'] . $this->parent . ':' . $this->cleanObject($context['object']);
            }
        } else {
            return '';
        }
    }

    public function bulk(array $baseContext, $bulkKey, array $values)
    {
        if ($bulkKey != 'object' || ! isset($baseContext['type'])) {
            return $values;
        }

        if ($this->parent && ! Perms::parentType($baseContext['type'])) {
            return $values;
        }

        $objects = [];
        $hashes = [];

        // Limit the amount of hashes preserved to reduce memory consumption
        if (count($this->known) > 1024) {
            $this->known = [];
        }

        foreach ($values as $v) {
            $hash = $this->getHash(array_merge($baseContext, [ 'object' => $v ]));
            if (! isset($this->known[$hash])) {
                $this->known[$hash] = [];
                $key = md5($baseContext['type'] . $this->cleanObject($v));
                $objects[$key] = $v;
                $hashes[$key] = $hash;
            }
        }

        if (count($objects) == 0) {
            return [];
        }

        $db = TikiDb::get();

        if ($baseContext['type'] === 'trackeritem' && $this->parent) {
            $bindvars = [];
            $result = $db->fetchAll(
                "SELECT md5(concat('trackeritem', LOWER(tti.`itemId`))) as `objectId`, op.`groupName`, op.`permName`
                FROM `tiki_tracker_items` tti, `users_objectpermissions` op
                WHERE op.`objectType` = 'tracker' AND op.`objectId` = md5(concat('tracker', LOWER(tti.`trackerId`))) AND " .
                $db->in('tti.itemId', array_values($objects), $bindvars),
                $bindvars
            );
        } elseif ($baseContext['type'] === 'file' && $this->parent) {
            $bindvars = [];
            $result = $db->fetchAll(
                "SELECT md5(concat('file', LOWER(tf.`fileId`))) as `objectId`, op.`groupName`, op.`permName`
                FROM `tiki_files` tf, `users_objectpermissions` op
                WHERE op.`objectType` = 'file gallery' AND op.`objectId` = md5(concat('file gallery', LOWER(tf.`galleryId`))) AND " .
                $db->in('tf.fileId', array_values($objects), $bindvars),
                $bindvars
            );
        } elseif ($baseContext['type'] === 'article' && $this->parent) {
            $bindvars = [];
            $result = $db->fetchAll(
                "SELECT md5(concat('article', LOWER(ta.`articleId`))) as `objectId`, op.`groupName`, op.`permName`
                FROM `tiki_articles` ta, `users_objectpermissions` op
                WHERE op.`objectType` = 'topic' AND op.`objectId` = md5(concat('topic', LOWER(ta.`topicId`))) AND " .
                $db->in('ta.articleId', array_values($objects), $bindvars),
                $bindvars
            );
        } elseif ($baseContext['type'] === 'blog post' && $this->parent) {
            $bindvars = [];
            $result = $db->fetchAll(
                "SELECT md5(concat('blog post', LOWER(tbp.`postId`))) as `objectId`, op.`groupName`, op.`permName`
                FROM `tiki_blog_posts` tbp, `users_objectpermissions` op
                WHERE op.`objectType` = 'blog' AND op.`objectId` = md5(concat('blog', LOWER(tbp.`blogId`))) AND " .
                $db->in('tbp.postId', array_values($objects), $bindvars),
                $bindvars
            );
        } elseif ($baseContext['type'] === 'thread' && $this->parent) {
            $bindvars = [];
            $result = $db->fetchAll(
                "SELECT md5(concat('thread', LOWER(tc.`threadId`))) as `objectId`, op.`groupName`, op.`permName`
                FROM `tiki_comments` tc, `users_objectpermissions` op
                WHERE op.`objectType` = 'forum' AND op.`objectId` = md5(concat('forum', LOWER(tc.`object`))) AND tc.`objectType` = 'forum' AND " .
                $db->in('tc.threadId', array_values($objects), $bindvars),
                $bindvars
            );
        } elseif ($baseContext['type'] === 'event' && $this->parent) {
            $bindvars = [];
            $result = $db->fetchAll(
                "SELECT md5(concat('event', LOWER(tci.`calitemId`))) as `objectId`, op.`groupName`, op.`permName`
                FROM `tiki_calendar_items` tci, `users_objectpermissions` op
                WHERE op.`objectType` = 'calendar' AND op.`objectId` = md5(concat('calendar', LOWER(tci.`calendarId`))) AND " .
                $db->in('tci.calitemId', array_values($objects), $bindvars),
                $bindvars
            );
        } else {
            $bindvars = [ $baseContext['type'] ];
            $result = $db->fetchAll(
                'SELECT `objectId`, `groupName`, `permName` FROM users_objectpermissions WHERE `objectType` = ? AND ' .
                $db->in('objectId', array_keys($objects), $bindvars),
                $bindvars
            );
        }
        $found = [];

        foreach ($result as $row) {
            $object = $row['objectId'];
            $group = $row['groupName'];
            $perm = $this->sanitize($row['permName']);
            $hash = (! empty($hashes[$object]) ? $hashes[$object] : ''); // TODO: maybe better: if empty($hashes[$object]) ==> continue;
            if (! empty($objects[$object])) {
                $found[] = $objects[$object];
            }

            if (! isset($this->known[$hash][$group])) {
                $this->known[$hash][$group] = [];
            }

            $this->known[$hash][$group][] = $perm;
        }

        return array_values(array_diff($values, $found));
    }

    public function getResolver(array $context)
    {
        if (! isset($context['type'], $context['object'])) {
            return null;
        }

        $hash = $this->getHash($context);

        $this->bulk($context, 'object', [ $context['object'] ]);

        if (isset($this->known[$hash])) {
            $perms = $this->known[$hash];
        } else {
            $perms = [];
        }

        if (count($perms) == 0) {
            return null;
        } else {
            return new Perms_Resolver_Static($perms, $this->parent ? 'parent' : 'object');
        }
    }

    private function sanitize($name)
    {
        if (strpos($name, 'tiki_p_') === 0) {
            return substr($name, strlen('tiki_p_'));
        } else {
            return $name;
        }
    }

    private function cleanObject($name)
    {
        return TikiLib::strtolower(trim($name));
    }
}
