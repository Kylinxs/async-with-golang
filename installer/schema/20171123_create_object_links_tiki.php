
<?php

use Tiki\Installer\Installer;

/**
 * Create links in tiki_links table for objects other than wiki pages,
 * comments and forum posts.
 *
 * @param \X\Installer $installer
 */
function upgrade_20171123_create_object_links_tiki($installer)
{
    include_once('tiki-setup.php');
    $maxRecordsPerQuery = 100;

    $create_links = function ($installer, $type, $objectId, $data) {
        $parserlib = TikiLib::lib('parser');
        $pages = $parserlib->get_pages($data);

        $linkhandle = "objectlink:$type:$objectId";

        foreach ($pages as $page) {
            $installer->query('REPLACE INTO `tiki_links` (`fromPage`, `toPage`) values (?, ?)', [$linkhandle, substr($page, 0, 158)]);
        }
    };

    /**
     * Blog posts
     */
    $table = $installer->table('tiki_blog_posts');

    $offset = 0;
    do {
        $items = $table->fetchAll([], [], $maxRecordsPerQuery, $offset);

        foreach ($items as $item) {
            $create_links($installer, 'post', $item['postId'], $item['data']);
        }
        $resultCount = count($items);
        $offset += $maxRecordsPerQuery;
    } while ($resultCount == $maxRecordsPerQuery);

    /**
     * Articles
     */
    $table = $installer->table('tiki_articles');

    $offset = 0;
    do {
        $items = $table->fetchAll([], [], $maxRecordsPerQuery, $offset);

        foreach ($items as $item) {
            $data = $item['heading'] . "\n" . $item['body'];
            $create_links($installer, 'article', $item['articleId'], $data);
        }
        $resultCount = count($items);
        $offset += $maxRecordsPerQuery;
    } while ($resultCount == $maxRecordsPerQuery);

    /**
     * Calendar events
     */
    $table = $installer->table('tiki_calendar_items');

    $offset = 0;
    do {
        $items = $table->fetchAll([], [], $maxRecordsPerQuery, $offset);

        foreach ($items as $item) {
            $create_links($installer, 'calendar event', $item['calitemId'], $item['description']);
        }
        $resultCount = count($items);
        $offset += $maxRecordsPerQuery;
    } while ($resultCount == $maxRecordsPerQuery);

    /**
     * Trackers
     */
    $table = $installer->table('tiki_trackers');

    $offset = 0;
    do {
        $items = $table->fetchAll([], [], $maxRecordsPerQuery, $offset);

        foreach ($items as $item) {
            if ($item['descriptionIsParsed'] == 'y') {
                $create_links($installer, 'tracker', $item['trackerId'], $item['description']);
            }
        }
        $resultCount = count($items);
        $offset += $maxRecordsPerQuery;
    } while ($resultCount == $maxRecordsPerQuery);

    /**
     * Tracker fields
     */
    $table = $installer->table('tiki_tracker_fields');

    $offset = 0;
    do {
        $items = $table->fetchAll([], [], $maxRecordsPerQuery, $offset);

        foreach ($items as $item) {
            if ($item['descriptionIsParsed'] == 'y') {
                $create_links($installer, 'trackerfield', $item['fieldId'], $item['description']);
            }
        }
        $resultCount = count($items);
        $offset += $maxRecordsPerQuery;
    } while ($resultCount == $maxRecordsPerQuery);


    /**
     * Tracker item fields
     */
    $trackerFields = $installer->table('tiki_tracker_fields');
    $itemFields = $installer->table('tiki_tracker_item_fields');
    foreach ($trackerFields->fetchAll(['fieldId'], ['type' => 'a']) as $field) {
        $fieldId = $field['fieldId'];

        $offset = 0;
        do {
            $items = $itemFields->fetchAll([], ['fieldId' => (int)$fieldId], $maxRecordsPerQuery, $offset);

            foreach ($items as $itemField) {
                $objectId = sprintf("%d:%d", (int)$itemField['itemId'], $fieldId);
                $create_links($installer, 'trackeritemfield', $objectId, $itemField['value']);
            }
            $resultCount = count($items);
            $offset += $maxRecordsPerQuery;
        } while ($resultCount == $maxRecordsPerQuery);
    }

    return true;
}