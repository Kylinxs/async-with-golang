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
            'eventTypes' => $this->getEventTypes(),
            'targetEvent' => $targetEvent,
            'targetTracker' => $targetTracker,
            'trackers' => TikiLib::lib('trk')->list_trackers(),
            'parameters' => $parameters,
        ];
    }

    /**
     * Create/update an advanced activity rule. Advanced rules are recorded by default.
     * @param JitFilter $request
     * @return array
     * @throws Math_Formula_Parser_Exception
     * @throws Services_Exception_FieldError
     */
    public function action_advanced(JitFilter $request)
    {
        $id = $request->ruleId->int();

        $util = new Services_Utilities();
        if ($util->isConfirmPost()) {
            $result = $this->replaceRule(
                $id,
                [
                    'rule' => $request->rule->text(),
                    'ruleType' => 'advanced',
                    'notes' => $request->notes->text(),
                    'eventType' => $request->event->attribute_type(),
                ],
                'rule'
            );
            //replaceRule sends error message so no need to here
            if ($result) {
                if ($id && $result->numRows()) {
                    Feedback::success(tr('Advanced activity rule %0 updated', $id));
                } elseif (! $id) {
                    Feedback::success(tr('Advanced activity rule %0 created', $result));
                } elseif (! $result->numRows()) {
                    Feedback::note(tr('Advanced activity rule %0 unchanged', $id));
                }
            }
        }

        return [
            'title' => $id ? tr('Edit Rule %0', $id) : tr('Create Advanced Rule'),
            'rule' => $this->getRule($id),
            'eventTypes' => $this->getEventTypes(),
        ];
    }

    /**
     * Private function to perform updating of rules
     * @param $id
     * @param array $data
     * @param $ruleField
     * @return TikiDb_Pdo_Result|TikiDb_Adodb_Result|integer $id    For a new item, $id will be the ID integer,
     *                                                                  otherwise a result class
     * @throws Services_Exception_FieldError
     */
    private function replaceRule($id, array $data, $ruleField)
    {
        try {
            $id = $this->lib->replaceRule($id, $data);

            return $id;
        } catch (Math_Formula_Exception $e) {
            throw new Services_Exception_FieldError($ruleField, $e->getMessage());
        }
    }

    /**
     * Private function listing activity rule types
     */
    private function getRuleTypes()
    {
        return [
            'sample' => tr('Sample'),
            'record' => tr('Basic'),
            'tracker_filter' => tr('Tracker'),
            'advanced' => tr('Advanced'),
        ];
    }

    /**
     * Private function to get available event types
     */
    private function getEventTypes()
    {
        $graph = TikiLib::events()->getEventGraph();
        sort($graph['nodes']);
        return $graph['nodes'];
    }

    /**
     * Private function to get details of an activity rule
     * @param int|Laminas\Filter\ToInt $id
     * @return array|mixed
     * @throws Math_Formula_Parser_Exception
     */
    private function getRule($id)
    {
        if (! $rule = $this->lib->getRule($id)) {
            $rule = [
                'ruleId' => null,
                'eventType' => '',
                'notes' => '',
                'rule' => '',
            ];
        }

        if ($rule['rule']) {
            $parser = new Math_Formula_Parser();
            $rule['element'] = $parser->parse($rule['rule']);
        } else {
            $rule['element'] = null;
        }

        return $rule;
    }

    /**
     * Change rule type for an activity rule. Sample rules can be changed to basic or advanced rule. Basic rule can be changed to advanced rule. Other type changes are not supported.
     * @param JitFilter $input
     * @return array
     * @throws Math_Formula_Parser_Exception
     * @throws Services_Exception_Denied
     * @throws Services_Exception_FieldError
     */
    public function action_change_rule_type(JitFilter $input)
    {
        $id = $input->ruleId->int();
        $rule = $this->getRule($id);
        $ruleTypes = $this->getRuleTypes();
        $currentRuleType = array_intersect_key($ruleTypes, array_flip(['ruleType' => $rule['ruleType']]));

        if ($rule['ruleType'] === 'sample') {
            $updateRuleTypes = [
                'record' => tr('Basic'),
                'advanced' => tr('Advanced'),
            ];
        } elseif ($rule['ruleType'] === 'record') {
            $updateRuleTypes = [
                'advanced' => tr('Advanced'),
            ];
        } else {
            throw new Services_Exception_Denied(tr('Invalid rule type'));
        }

        $util = new Services_Utilities();
        if ($util->isConfirmPost()) {
            $currentRuleType = $rule['ruleType'];
            $newRuleType = $input->ruleType->text();
            //if sample is changed to basic or advanced, "event-sample" needs to be changed to "event-record" in the rule
            if ($currentRuleType === 'sample') {
                $rule['rule'] = str_replace('event-sample', 'event-record', $rule['rule']);
            }

            $result = $this->replaceRule(
                $id,
                [
                    'rule' => $rule['rule'],
                    'ruleType' => $newRuleType,
                    'notes' => $rule['notes'],
                    'eventType' => $rule['eventType'],
                ],
                'notes'
            );
            //replaceRule sends error message so no need to here
            if ($result->numRows()) {
                Feedback::success(tr('Type changed for activity rule %0', $id));
            }
        }

        return [
            'title' => tr('Change Rule Type'),
            'rule' => $rule,
            'currentRuleType' => $currentRuleType,
            'ruleTypes' => $updateRuleTypes,
        ];
    }

    /**
     * Enable/disable an activity rule. Can be used for basic and advanced types. Tracker type is always enabled, sample type is always disabled, so no need to manage them.
     * @param JitFilter $input
     * @return array
     * @throws Math_Formula_Parser_Exception
     * @throws Services_Exception_FieldError
     */
    public function action_change_rule_status(JitFilter $input)
    {
        $id = $input->ruleId->int();
        $rule = $this->getRule($id);
        $status = $this->getRuleStatus($id);

        $util = new Services_Utilities();
        if ($util->isConfirmPost()) {
            //to disable a rule "event-record" needs to be changed to "event-sample" in the rule
            if (($rule['ruleType'] === 'record' || $rule['ruleType'] === 'advanced') && $status === 'enabled') {
                $rule['rule'] = str_replace('event-record', 'event-sample', $rule['rule']);
                //to enable a rule "event-sample" needs to be changed to "event-record" in the rule
            } elseif (($rule['ruleType'] === 'record' || $rule['ruleType'] === 'advanced') && $status === 'disabled') {
                $rule['rule'] = str_replace('event-sample', 'event-record', $rule['rule']);
            }

            $result = $this->replaceRule(
                $id,
                [
                    'rule' => $rule['rule'],
                    'ruleType' => $rule['ruleType'],
                    'notes' => $rule['notes'],
                    'eventType' => $rule['eventType'],
                ],
                'notes'
            );
            //replaceRule sends error message so no need to here
            if ($result->numRows()) {
                Feedback::success(tr('Status changed for activity rule %0', $id));
            }
        }

        return [
            'title' => tr('Change Rule Status'),
            'rule' => $rule,
            'status' => $status,
        ];
    }

    /**
     * Private function to get the status of an activity rule
     * @param int|Laminas\Filter\ToInt $id
     * @return string
     * @throws Math_Formula_Parser_Exception
     */
    private function getRuleStatus($id)
    {
        $rule = $this->getRule($id);
        $ruleCommandRaw = explode(' ', $rule['rule']);
        $ruleCommand = str_replace('(', '', $ruleCommandRaw[0]);
        if ($ruleCommand === 'event-sample') {
            return 'disabled';
        }
        if ($ruleCommand === 'event-record' || $ruleCommand === 'event-notify' || $rule['ruleType'] === 'tracker_filter') {
            return 'enabled';
        } else {
            return 'unknown';
        }
    }
}
