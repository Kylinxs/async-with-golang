
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Search_ContentSource_ForumPostSource implements Search_ContentSource_Interface, Tiki_Profile_Writer_ReferenceProvider
{
    private $db;

    public function __construct()
    {
        $this->db = TikiDb::get();
    }

    public function getReferenceMap()
    {
        return [
            'forum_id' => 'forum',
        ];
    }

    public function getDocuments()
    {
        global $prefs;
        if ($prefs['unified_forum_deepindexing'] == 'y') {
            $filters = ['objectType' => 'forum', 'parentId' => 0];
        } else {
            $filters = ['objectType' => 'forum'];
        }
        return $this->db->table('tiki_comments')->fetchColumn('threadId', $filters);
    }

    public function getDocument($objectId, Search_Type_Factory_Interface $typeFactory)
    {
        global $prefs;

        /** @var Comments $commentslib */
        $commentslib = TikiLib::lib('comments');
        $commentslib->extras_enabled(false);
        $comment = $commentslib->get_comment($objectId);

        if (! $comment) {
            return false;
        }

        $root_thread_id = $commentslib->find_root($comment['parentId']);
        if ($comment['parentId']) {
            $root = $commentslib->get_comment($root_thread_id);
            if (! $comment['title']) {
                $comment['title'] = $root['title'];
            }
            $root_author = [$root['userName']];
        } else {
            $root_author = [];
        }

        $lastModification = $comment['commentDate'];
        $content = $comment['data'];
        $snippet = TikiLib::lib('tiki')->get_snippet($content);
        $author = [$comment['userName']];

        $thread = $commentslib->get_comments($comment['objectType'] . ':' . $comment['object'], $objectId, 0, 0);
        $forum_info = $commentslib->get_forum($comment['object']);
        $forum_language = $forum_info['forumLanguage'] ?? 'unknown';

        if ($prefs['unified_forum_deepindexing'] == 'y') {
            foreach ($thread['data'] as $reply) {
                $content .= "\n{$reply['data']}";
                $lastModification = max($lastModification, $reply['commentDate']);
                $author[] = $comment['userName'];
            }
        }

        $commentslib->extras_enabled(true);

        $data = [
            'title' => $typeFactory->sortable($comment['title']),
            'language' => $typeFactory->identifier($forum_language),
            'creation_date' => $typeFactory->timestamp($comment['commentDate']),
            'modification_date' => $typeFactory->timestamp($lastModification),
            'date' => $typeFactory->timestamp($comment['commentDate']),
            'contributors' => $typeFactory->multivalue(array_unique($author)),

            'forum_id' => $typeFactory->identifier($comment['object']),
            'forum_section' => $typeFactory->identifier($forum_info['section'] ?? ''),
            'forum_title' => $typeFactory->sortable($forum_info['name'] ?? ''),

            'post_content' => $typeFactory->wikitext($content),
            'post_author' => $typeFactory->wikitext($comment['userName']),
            'post_snippet' => $typeFactory->plaintext($snippet),
            'parent_thread_id' => $typeFactory->identifier($comment['parentId']),

            'parent_object_type' => $typeFactory->identifier($comment['objectType']),
            'parent_object_id' => $typeFactory->identifier($comment['object']),
            'view_permission' => $typeFactory->identifier('tiki_p_forum_read'),
            '_permission_accessor' => Perms::get('thread', $objectId, $comment['object']),
            'parent_contributors' => $typeFactory->multivalue(array_unique($root_author)),
            'hits' => $typeFactory->numeric($comment['hits']),
            'root_thread_id' => $typeFactory->identifier($root_thread_id),
            'thread_type' => $typeFactory->identifier($comment['type']),
            'reply_count' => $typeFactory->numeric(count($thread['data'])),
            'locked' => $typeFactory->identifier($comment['locked']),
        ];

        $forum_lastPost = $this->getForumLastPostData($objectId, $typeFactory);

        $data = array_merge($data, $forum_lastPost);

        return $data;
    }

    /**
     * Return data array of last post for thread
     *
     * @param $threadId
     * @param Search_Type_Factory_Interface $typeFactory
     * @return array
     * @throws Exception
     */
    public function getForumLastPostData($threadId, Search_Type_Factory_Interface $typeFactory)
    {
        $commentslib = TikiLib::lib('comments');
        $commentslib->extras_enabled(false);

        $comment = $commentslib->get_lastPost($threadId);

        $lastModification = isset($comment['commentDate']) ? $comment['commentDate'] : 0;
        $content = isset($comment['data']) ? $comment['data'] : '';
        $snippet = TikiLib::lib('tiki')->get_snippet($content);
        $author = [isset($comment['userName']) ? $comment['userName'] : ''];

        $commentslib->extras_enabled(true);

        $data = [
            'lastpost_title' => $typeFactory->sortable(isset($comment['title']) ? $comment['title'] : ''),
            'lastpost_modification_date' => $typeFactory->timestamp($lastModification),
            'lastpost_contributors' => $typeFactory->multivalue(array_unique($author)),
            'lastpost_post_content' => $typeFactory->wikitext($content),
            'lastpost_post_snippet' => $typeFactory->plaintext($snippet),
            'lastpost_hits' => $typeFactory->numeric(isset($comment['hits']) ? $comment['hits'] : 0),
            'lastpost_thread_id' => $typeFactory->identifier(isset($comment['thread_id']) ? $comment['thread_id'] : 0),
        ];

        return $data;
    }

    public function getProvidedFields()
    {
        return [
            'title',
            'language',
            'creation_date',
            'modification_date',
            'date',
            'contributors',

            'post_content',
            'post_author',
            'post_snippet',
            'forum_id',
            'forum_section',
            'forum_title',
            'parent_thread_id',

            'view_permission',
            'parent_object_id',
            'parent_object_type',

            'root_thread_id',
            'parent_contributors',
            'hits',
            'thread_type',
            'reply_count',
            'locked',

            'lastpost_title',
            'lastpost_modification_date',
            'lastpost_contributors',
            'lastpost_post_content',
            'lastpost_post_snippet',
            'lastpost_hits',
            'lastpost_thread_id',
        ];
    }

    public function getProvidedFieldTypes()
    {
        return [
            'title' => 'sortable',
            'language' => 'identifier',
            'creation_date' => 'timestamp',
            'modification_date' => 'timestamp',
            'date' => 'timestamp',
            'contributors' => 'multivalue',

            'post_content' => 'wikitext',
            'post_author' => 'wikitext',
            'post_snippet' => 'plaintext',
            'forum_id' => 'identifier',
            'forum_section' => 'identifier',
            'forum_title' => 'sortable',
            'parent_thread_id' => 'identifier',

            'view_permission' => 'identifier',
            'parent_object_id' => 'identifier',
            'parent_object_type' => 'identifier',

            'root_thread_id' => 'identifier',
            'parent_contributors' => 'multivalue',
            'hits' => 'numeric',
            'thread_type' => 'identifier',
            'reply_count' => 'numeric',
            'locked' => 'identifier',

            'lastpost_title' => 'sortable',
            'lastpost_modification_date' => 'timestamp',
            'lastpost_contributors' => 'multivalue',
            'lastpost_post_content' => 'wikitext',
            'lastpost_post_snippet' => 'plaintext',
            'lastpost_hits' => 'numeric',
            'lastpost_thread_id' => 'identifier',
        ];
    }

    public function getGlobalFields()
    {
        return [
            'title' => true,
            'date' => true,

            'post_content' => false,
        ];
    }
}