<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Tiki_Profile_InstallHandler_ArticleTopic extends Tiki_Profile_InstallHandler
{
    public function getData()
    {
        if ($this->data) {
            return $this->data;
        }

        $data = $this->obj->getData();
        $data = Tiki_Profile::convertYesNo($data);

        return $this->data = $data;
    }

    public function canInstall()
    {
        $data = $this->getData();

        if (! isset($data['name'])) {
            return false;
        }

        return true;
    }

    public function doInstall()
    {
        $artlib = TikiLib::lib('art');
        $data = $this->getData();

        $this->replaceReferences($data);

        $id = $artlib->add_topic($data['name'], null, null, null, null);

        return $id;
    }

    public static function export(Tiki_Profile_Writer $writer, $topicId)
    {
        $artlib = TikiLib::lib('art');
        $info = $artlib->get_topic($topicId);

        if ($info) {
            $writer->addObject('article_topic', $topicId, [
                'name' => $info['name'],
            ]);

            return true;
        }

        return false;
    }

    /**
     * Remove article topic
     *
     * @param string $articleTopic
     * @return bool
     */
    public function remove($articleTopic)
    {
        if (! empty($articleTopic)) {
            $artlib = TikiLib::lib('art');
            $articleTopicId = $artlib->get_topicId($articleTopic);
            if (! empty($articleTopicId) && $artlib->remove_topic($articleTopicId)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get current article topic data
     *
     * @param array $article
     * @return mixed
     */
    public function getCurrentData($articleTopic)
    {
        $articleTopicName = ! empty($articleTopic['name']) ? $articleTopic['name'] : '';
        if (! empty($articleTopicName)) {
            $artlib = TikiLib::lib('art');
            $articleTopicId = $artlib->get_topicId($articleTopicName);
            if (! empty($articleTopicId)) {
                $articleTopicData = $artlib->get_topic($articleTopicId);
                return $articleTopicData;
            }
        }
        return false;
    }
}
