<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Services_User_MonitorController
{
    public function setUp()
    {
        Services_Exception_Disabled::check('monitor_enabled');
        Services_Exception_Denied::checkAuth();
    }

    public function action_object($input)
    {
        global $user;

        $type = $input->type->text();
        $object = $input->object->text();

        $objectlib = TikiLib::lib('object');
        $title = $objectlib->get_title($type, $object);

        $monitorlib = TikiLib::lib('monitor');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $options = $monitorlib->getOptions($user, $type, $object);
            foreach ($options as $option) {
                $key = $option['hash'];
                $selected = $input->notification->{$key}->word();

                if ($option['priority'] != $selected) {
                    $monitorlib->replacePriority($user, $option['event'], $option['target'], $selected);
                }
            }
        }

        return [
            'type' => $type,
            'object' => $object,
            'title' => tr('Notifications for %0', $title),
            'options' => $monitorlib->getOptions($user, $type, $object),
            'priorities' => $monitorlib->getPriorities(),
        ];
    }

    public function action_set_component_last_view($input)
    {
        global $user;

        if (! $user) {
            return;
        }

        $tikiLib = TikiLib::lib('tiki');

        $component = $input->component->text();
        $id = $input->id->int();

        $prefName = "last_viewed_";

        if (! empty($id)) {
            $prefName .= $component . "_" . $id;
        } else {
            $prefName .= $component;
        }

        $tikiLib->set_user_preference($user, $prefName, time());
    }

    public function action_stream($input)
    {
        $loginlib = TikiLib::lib('login');

        $userId = $loginlib->getUserId();

        $critical = $input->critical->int();
        $high = $input->high->int();
        $low = $input->low->int();

        $from = $input->from->text();
        $to = $input->to->text();

        if (! $critical && ! $high && ! $low) {
            throw new Services_Exception_NotFound();
        }

        global $user;
        // get the groups this user is in
        $user_groups = TikiLib::lib('tiki')->get_user_groups($user);

        // get the id's from the users_groups table using the groupName
        $where = " WHERE groupName IN (" . implode(',', array_fill(0, count($user_groups), '?')) . ')';
        $query = "select id from users_groups" . $where;
        $group_ids = TikiLib::lib('tiki')->fetchAll($query, $user_groups);
        $group_ids = array_column($group_ids, 'id');

        // set up strings to append to our queries (pull group notifications)
        $critical_groups = '';
        $high_groups = '';
        $low_groups = '';
        foreach ($group_ids as $group_id) {
            $critical_groups .= " OR criticalgrp$group_id ";
            $high_groups .= " OR highgrp$group_id ";
            $low_groups .= " OR lowgrp$group_id ";
        }

        $searchlib = TikiLib::lib('unifiedsearch');
        $query = $searchlib->buildQuery([
            'type' => 'activity',
        ]);
        $query->setOrder('modification_date_desc');

        $sub = $query->getSubQuery('optional');
        if ($critical) {
            $sub->filterMultivalue("critical$userId $critical_groups", "stream");
        }
        if ($high) {
            $sub->filterMultivalue("high$userId $high_groups", "stream");
        }
        if ($low) {
            $sub->filterMultivalue("low$userId $low_groups", "stream");
        }

        if ($from && $to) {
            $query->filterRange($from, $to);
        }

        $query->setRange($input->offset->int(), $input->limit->int());

        $result = $query->search($searchlib->getIndex());

        if (! $result->count()) {
            throw new Services_Exception(tr('No notifications.'), 404);
        }

        // Hacking around the horrible code generating urls in pagination
        $_GET = [
            'critical' => $critical, 'high' => $high, 'low' => $low,
            'from' => $from, 'to' => $to,
        ];
        $service = ['controller' => 'monitor', 'action' => 'stream'];

        global $prefs;
        $servicelib = TikiLib::lib('service');
        if ($prefs['feature_sefurl'] == 'y') {
            $_SERVER['PHP_SELF'] = $servicelib->getUrl($service);
        } else {
            $_GET += $service;
            $_SERVER['PHP_SELF'] = 'tiki-ajax_services.php';
        }

        return [
            'title' => tr('Notifications'),
            'result' => $result,
        ];
    }

    public function action_unread($input)
    {
        global $user;
        $loginlib = TikiLib::lib('login');
        $servicelib = TikiLib::lib('service');
        $tikilib = TikiLib::lib('tiki');

        $lastread = $tikilib->get_user_preference($user, 'notification_read', 1388534400); // Jan 2014, as the feature did not exist prior to this date anyway

        $userId = $loginlib->getUserId();

        // get the groups this user is in
        $user_groups = TikiLib::lib('tiki')->get_user_groups($user);

        // get the id's from the users_groups table using the groupName
        $where = " WHERE groupName IN (" . implode(',', array_fill(0, count($user_groups), '?')) . ')';
        $query = "select id from users_groups" . $where;
        $group_ids = TikiLib::lib('tiki')->fetchAll($query, $user_groups);
        $group_ids = array_column($group_ids, 'id');

        // set up string to append to our query (pull group notifications)
        $or_groups = '';
        foreach ($group_ids as $group_id) {
            $or_groups .= " OR criticalgrp$group_id OR highgrp$group_id OR lowgrp$group_id ";
        }

        $searchlib = TikiLib::lib('unifiedsearch');
        $query = $searchlib->buildQuery([
            'type' => 'activity',
        ]);
        $query->filterMultivalue("critical$userId OR high$userId OR low$userId $or_groups", 'stream');
        $query->filterRange($lastread, 'now');
        $query->filterMultivalue("NOT \"$user\"", 'clear_list');
        $query->setOrder('modification_date_desc');

        if ($input->nodata->int()) {
            $query->setRange(0, 1);
        } else {
            $query->setRange(0, 7);
        }
        $result = $query->search($searchlib->getIndex());

        return [
            'title' => tr('Unread Notifications'),
            'count' => count($result),
            'result' => $result,
            'timestamp' => TikiLib::lib('tiki')->now,
            'more_link' => $servicelib->getUrl([
                'controller' => 'monitor',
                'action' => 'stream',
                'from' => '-30 days',
                'to' => 'now',
                'critical' => 1,
                'high' => 1,
                'low' => 1,
            ]),
        ];
    }

    public function action_clearall($input)
    {
        global $user;

        $tikilib = TikiLib::lib('tiki');
        $timestamp = $input->timestamp->int();

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $timestamp) {
            $tikilib->set_user_preference($user, 'notification_read', $timestamp);
        }

        return [
            'title' => tr('Mark all notifications as read'),
            'timestamp' => $timestamp ?: $tikilib->now,
        ];
    }

    public function action_clearone($input)
    {
        Services_Exception_Disabled::check('monitor_individual_clear');

        global $user;
        $relationlib = TikiLib::lib('relation');
        $searchlib = TikiLib::lib('unifiedsearch');

        $activity = $input->activity->int();

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $activity) {
            $relationlib->add_relation('tiki.monitor.cleared', 'user', $user, 'activity', $activity);
            $searchlib->invalidateObject('activity', $activity);
            $searchlib->processUpdateQueue();
        }
    }
}
