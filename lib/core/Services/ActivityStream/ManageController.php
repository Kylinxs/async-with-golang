<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Services_ActivityStream_ManageController
{
    /**
     * @var ActivityLib
     */
    private $lib;

    /**
     * Set up the controller
     */
    public function setUp()
    {
        if (! Perms::get()->admin) {
            throw new Services_Exception(tr('Permission Denied'), 403);
        }

        $this->lib = TikiLib::lib('activity');
    }

    /**
     * List activity rules from tiki_activity_stream_rules table
     * @return array
     * @throws Math_Formula_Parser_Exception
     */
    public function action_list()
    {
        $rules = $this->lib->getRules();

        foreach ($rules as &$rule) {
            $status = $this->getRuleStatus($rule['ruleId']);
            $rule['status'] = $status;
        }

        return [
            'rules' => $rules,
            'ruleTypes' => $this->getRuleTypes(),
            'event_graph' => TikiLib::events()->getEventGraph(),
        ];
    }

    /**
     * Delete an activity rule from tiki_activity_stream_rules table
     * @param JitFilter $request
     * @return array
     * @throws Math_Formula_Parser_Exception
     */
    public function action_delete(JitFilter $request)
    {
        $id = $request->ruleId->int();
        $rule = $this->getRule($id);

        $removed = false;
        $util = new Services_Utilities();
        if ($util->isConfirmPost()) {
            /** @var TikiDb_Pdo_Result|TikiDb_Adodb_Result $result */
            $result = $this->lib->deleteRule($id);
            if ($result->numRows()) {
                if ($result->numRows() == 1) {
                    Feedback::success(tra('Activity rule deleted'));
                } else {
                    Feedback::success(tra('%0 activity rules deleted', $result->numRows()));
                }
            } else {
                Feedback::error(tra('No activity rules deleted'));
            }
            $removed = true;
        }

        return [
            'title' => tr('Delete Rule'),
            'removed' => $removed,
            'rule' => $rule,
            'eventTypes' => $this->getEventTypes(),
        ];
    }

    /**
     * Delete a recorded activity from tiki_activity_stream table
     * @param JitFilter $request
     * @return array
     * @throws Exception
     */
    public function action_deleteactivity(JitFilter $request)
    {
        $id = $request->activityId->int();

        $util = new Services_Utilities();
        if ($util->isConfirmPost()) {
            /** @var TikiDb_Pdo_Result|TikiDb_Adodb_Result $result */
            $result = $this->lib->deleteActivity($id);
            if ($result->numRows()) {
                Feedback::success(tr('Activity (id:' . (string) $id . ') deleted'));
            } else {
                Feedback::error(tra('No activities deleted'));
            }
        }

        return [
            'title' => tra('Delete Activity'),
            'activityId' => $id,
        ];
    }

    /**
     * Create/update a sample activity rule. Sample rules are never recorded.
     * @param JitFilter $request
     * @return array
     * @throws Math_Formula_Parser_Exception
     * @throws Services_Exception_FieldError
     */
    public function action_sample(JitFilter $request)
    {
        $id = $request->ruleId->int();

        $util = new Services_Utilities();
        if ($util->isConfirmPost()) {
            $event = $request->event->attribute_type();
            $result = $this->replaceRule(
                $id,
                [
                    'rule' => "(event-sample (str $event) event args)",
                    'ruleType' => 'sample',
                    'notes' => $request->notes->text(),
                    'eventType' => $event,
                ],
                'event'
            );
            //replaceRule sends error message so no need to here
            if ($result) {
                if ($id && $result->numRows()) {
                    Feedback::success(tr('Sample activity rule %0 updated', $id));
                } elseif (! $id) {
                    Feedback::success(tr('Sample activity rule %0 created', $result));
                } elseif (! $result->numRows()) {
                    Feedback::note(tr('Sample activity rule %0 unchanged', $id));
                }
            }
        }

        $rule = $this->getRule($id);

        $getEventTypes = $this->getEventTypes();
        foreach ($getEventTypes as $key => $eventType) {
            $eventTypes[$key]['eventType'] = $eventType;
            $sample = $this->lib->getSample($eventType);
            if (! empty($sample)) {
                $eventTypes[$key]['sample'] = $sample;
            }
        }

        return [
            'title' => $id ? tr('Edit Rule %0', $id) : tr('Create Sample Rule'),
            'data' => $this->lib->getSample($rule['eventType']),
            'rule' => $rule,
            'eventTypes' => $eventTypes,
        ];
    }

    /**
     * Create/update a basic activity rule. Basic rules are recorded by default.
     * @param JitFilter $request
     * @return array
     * @throws Math_Formula_Parser_Exception
     * @throws Services_Exception_FieldError
     */
    public function action_record(JitFilter $request)
    {
        $id = $request->ruleId->int();
        $priority = $request['priority'];
        $user = $request['user'];

        if ($request['is_notification'] != "on") {
            $rule = '(event-record event args)';
        } else {
            $rule = "(event-notify event args (str $priority) (str $user))";
        }

        $util = new Services_Utilities();
        if ($util->isConfirmPost()) {
            $result = $this->replaceRule(
                $id,
                [
                    'rule' => $rule,
                    'ruleType' => 'record',
                    'notes' => $request->notes->text(),
                    'eventType' => $request->event->attribute_type(),
                ],
                'notes'
            );
            //replaceRule sends error message so no need to here
            if ($result) {
                if ($id && $result->numRows()) {
                    Feedback::success(tr('Basic activity rule %0 updated', $id));
                } elseif (! $id) {
                    Feedback::success(tr('Basic activity rule %0 created', $result));
                } elseif (! $result->numRows()) {
                    Feedback::note(tr('Basic activity rule %0 unchanged', $id));
                }
            }
        }

        return [
            'title' => $id ? tr('Edit Rule %0', $id) : tr('Create Record Rule'),
            'rule' => $this->getRule($id),
            'eventTypes' => $this->getEventTypes(),
        ];
    }

    /**
     * Create/update a tracker_filter activity rule. Tracker rules are recorded and linked to a tracker.
     * @param JitFilter $request
     * @return array
     * @throws Math_Formula_Parser_Exception
     * @throws Services_Exception_FieldError
     * @throws Services_Exception_MissingValue
     */
    public function action_tracker_filter(JitFilter $request)
    {
        $id = $request->ruleId->int();

        $util = new Services_Utilities();
        if ($util->isConfirmPost()) {
            $tracker = $request->tracker->int();
            $targetEvent = $request->targetEvent->attribute_type();
            $customArguments = $request->parameters->text();

            if (! $targetEvent) {
                throw new Services_Exception_MissingValue('targetEvent');
            }

            $result = $this->replaceRule(
                $id,
                [
                    'rule' => "
(if (equals args.trackerId $tracker) (event-trigger $targetEvent (map
$customArguments
)))
",
                    'ruleType' => 'tracker_filter',
                    'notes' => $request->notes->text(),
                    'eventType' => $request->sourceEvent->attribute_type(),
                ],
                'parameters'
            );
            //replaceRule sends error message so no need to here
            if ($result) {
                if ($id && $result->numRows()) {
                    Feedback::success(tr('Tracker activity rule %0 updated', $id));
                } elseif (! $id) {
                    Feedback::success(tr('Tracker activity rule %0 created', $result));
                } elseif (! $result->numRows()) {
                    Feedback::note(tr('Tracker activity rule %0 unchanged', $id));
                }
            }
        }

        $rule = $this->getRule($id);
        $root = $rule['element'];
        $parameters = '';
        $targetTracker = null;
        $targetEvent = null;

        if ($root) {
            $targetTracker = (int) $root->equals[1];
            $targetEvent = $root->{'event-trigger'}[0];
            foreach ($root->{'event-trigger'}->map as $element) {
                $parameters .= '(' . $element->getType() . ' ' . $element[0] . ')' . PHP_EOL;
            }
        } else {
            $parameters = "(user args.user)\n(type args.type)\n(object args.object)\n(aggregate args.aggregate)\n";
        }

        return [
            'title' => $id ? tr('Edit Rule %0', $id) : tr('Create Tracker Rule'),
            'rule' => $rule,
            'eventTypes' => 