
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Search_ContentSource_CommentSource implements Search_ContentSource_Interface
{
    private $types;
    private $db;
    private $permissionMap;
    private $indexer;

    public function __construct($types)
    {
        $this->types = $types;

        $this->db = TikiDb::get();

        $this->permissionMap = TikiLib::lib('object')->map_object_type_to_permission(true);
    }

    public function getDocuments()
    {
        $comments = $this->db->table('tiki_comments');

        return $comments->fetchColumn(
            'threadId',
            [
                'objectType' => $comments->in($this->types),
            ]
        );
    }

    public function getDocument($objectId, Search_Type_Factory_Interface $typeFactory)
    {
        $commentslib = TikiLib::lib('comments');

        if ($this->indexer) {
            $object = $commentslib->get_comment_object($objectId);
            if ($object) {
                $this->indexer->errorContext = 'Comment owner ' . $object['objectType'] . ' ' . $object['object'];
            } else {
                $this->indexer->errorContext = 'Comment owner (can not find object ' . $objectId . ')';
            }
        }

        $comment = $commentslib->get_comment($objectId);

        if ($this->indexer) {
            $this->indexer->errorContext = null;
        }

        if (! $comment) {
            return false;
        }

        $url = $commentslib->getHref($comment['objectType'], $comment['object'], $objectId);
        $url = str_replace('&amp;', '&', $url);

        $data = [
            'title' => $typeFactory->sortable($comment['title']),
            'language' => $typeFactory->identifier('unknown'),
            'creation_date' => $typeFactory->timestamp($comment['commentDate']),
            'modification_date' => $typeFactory->timestamp($comment['commentDate']),
            'date' => $typeFactory->timestamp($comment['commentDate']),
            'contributors' => $typeFactory->multivalue([$comment['userName']]),

            'comment_content' => $typeFactory->wikitext($comment['data']),
            'parent_thread_id' => $typeFactory->identifier($comment['parentId']),

            'parent_object_type' => $typeFactory->identifier($comment['objectType']),
            'parent_object_id' => $typeFactory->identifier($comment['object']),
            'view_permission' => $typeFactory->identifier($this->getParentPermissionForType($comment['objectType'])),
            'global_view_permission' => $typeFactory->identifier('tiki_p_read_comments'),

            'url' => $typeFactory->identifier($url),
        ];

        if ($comment['objectType'] == 'trackeritem') {
            $item = TikiLib::lib('trk')->get_tracker_item($comment['object']);
            if (! empty($item)) {
                $itemObject = Tracker_Item::fromInfo($item);
                if (! empty($itemObject) && $itemObject->getDefinition()) {
                    $specialUsers = $itemObject->getSpecialPermissionUsers($comment['object'], 'View');
                    $ownerGroup = $itemObject->getOwnerGroup();
                    $data = array_merge($data, [
                        '_extra_users' => $specialUsers,
                        '_permission_accessor' => $itemObject->getPerms(),
                        '_extra_groups' => $ownerGroup ? [$ownerGroup] : null,
                    ]);
                }
                $data['tracker_id'] = $typeFactory->identifier($item['trackerId']);
            }
        }

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
            'url',

            'comment_content',
            'parent_thread_id',
            'parent_object_id',
            'parent_object_type',

            'view_permission',
            'global_view_permission',
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
            'url' => 'identifier',

            'comment_content' => 'wikitext',
            'parent_thread_id' => 'identifier',
            'parent_object_id' => 'identifier',
            'parent_object_type' => 'identifier',

            'view_permission' => 'identifier',
            'global_view_permission' => 'identifier',
        ];
    }

    public function getGlobalFields()
    {
        return [
            'title' => true,
            'date' => true,

            'comment_content' => true,
        ];
    }

    private function getParentPermissionForType($type)
    {
        if (isset($this->permissionMap[$type])) {
            return $this->permissionMap[$type];
        }
    }

    public function setIndexer($indexer)
    {
        $this->indexer = $indexer;
    }
}