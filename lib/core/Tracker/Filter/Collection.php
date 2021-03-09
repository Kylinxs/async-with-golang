
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tracker\Filter;

use Search_Query;
use TikiLib;

class Collection
{
    private $definition;
    private $filters = [];
    private $collections = [];
    private $resultset;

    public function __construct(\Tracker_Definition $definition)
    {
        $this->definition = $definition;
    }

    public static function getFilter($fieldName, $mode, $isTrackerField)
    {
        $field = TikiLib::lib('trk')->get_field_by_perm_name($fieldName);
        if ($isTrackerField && $field) {
            if (! isset($collection)) {
                $definition = \Tracker_Definition::get($field['trackerId']);
                $collection = new self($definition);
            }
            try {
                return $collection->addFilter($field['permName'], $mode);
            } catch (Exception\ModeNotSupported $e) {
                return $collection->addFilter($field['permName']);
            }
        } else {
            // non-tracker field in the index
            $filter = new Filter($fieldName, 'default');
            $filter->setLabel($fieldName)
                ->setControl(new Control\TextField("tf_" . $fieldName))
                ->setApplyCondition(function ($control, Search_Query $query) use ($fieldName) {
                    $value = $control->getValue();
                    if ($value) {
                        $query->filterContent($value, $fieldName);
                    }
                });
            return $filter;
        }
    }

    public function addNew($permName, $mode)
    {
        $column = new Filter($permName, $mode);
        $this->filters[] = $column;
        return $column;
    }

    public function addCloned($permName, self $collection)
    {
        foreach ($collection->filters as $filter) {
            $this->addNew($permName, $filter->getMode())
                ->copyProperties($filter);
        }
    }

    public function getFilters()
    {
        return $this->filters;
    }

    public function applyConditions(\Search_Query $query)
    {
        foreach ($this->filters as $filter) {
            $filter->applyCondition($query);
        }
    }

    public function applyInput(\JitFilter $input)
    {
        foreach ($this->filters as $filter) {
            $filter->applyInput($input);
        }
    }

    public function loadFilterDescriptor($descriptor)
    {
        foreach ($descriptor as $filter) {
            try {
                $fil = $this->addFilter($filter['field'], $filter['mode']);

                if (! empty($filter['position'])) {
                    $fil->setPosition($filter['position']);
                }

                if (! empty($filter['label'])) {
                    $fil->setLabel($filter['label']);
                }
            } catch (Exception\FieldNotFound $e) {
                \Feedback::error($e->getMessage()); // TODO make error message appear when exporting
            }
        }
    }

    public function addFilter($permName, $mode = null)
    {
        if (isset($this->collections[$permName])) {
            $partial = $this->collections[$permName];
        } else {
            $partial = $this->getFieldCollection($permName);
            $this->collections[$permName] = $partial;
        }

        $filter = $partial->lookupMode($permName, $mode);
        $this->filters[] = $filter;

        return $filter;
    }

    private function lookupMode($permName, $mode)
    {
        foreach ($this->filters as $filter) {
            if ($filter->getField() == $permName && (! $mode || $filter->getMode() == $mode)) {
                return $filter;
            }
        }

        throw new Exception\ModeNotSupported($permName, $mode);
    }

    public function getFieldCollection($permName)
    {
        if ($partial = $this->getSystemCollection($permName)) {
            return $partial;
        }

        $field = $this->definition->getFieldFromPermName($permName);
        $factory = $this->definition->getFieldFactory();

        if (! $field) {
            throw new Exception\FieldNotFound($permName);
        }

        $handler = $factory->getHandler($field);

        if (! $handler instanceof \Tracker_Field_Filterable) {
            throw new Exception\ModeNotSupported($permName, 'any mode');
        }

        return $handler->getFilterCollection();
    }

    private function getSystemCollection($name)
    {
        switch ($name) {
            case 'itemId':
                $collection = new self($this->definition);
                $collection->addNew($name, 'lookup')
                ->setLabel(tr('Item ID'))
                ->setHelp(tr('Look up a single item by ID.'))
                ->setControl(new Control\TextField("tf_itemId"))
                ->setApplyCondition(function ($control, Search_Query $query) {
                    $value = $control->getValue();

                    if ($value) {
                        $query->filterIdentifier($value, 'object_id');
                    }
                })
                    ;
                return $collection;
            case 'search':
                $collection = new self($this->definition);
                $collection->addNew($name, 'search')
                ->setLabel(tr('Search'))
                ->setHelp(tr('Full-text search across all of the content.'))
                ->setControl(new Control\TextField("tf_search"))
                ->setApplyCondition(function ($control, Search_Query $query) {
                    $value = $control->getValue();

                    if ($value) {
                        $o = TikiLib::lib('tiki')->get_preference('unified_default_content', ['contents'], true);
                        if (count($o) == 1 && empty($o[0])) {
                            // Use "contents" field by default, if no default is specified
                            $query->filterContent($value, ['contents']);
                        } else {
                            $query->filterContent($value, $o);
                        }
                    }
                })
                    ;
                return $collection;
            case 'status':
                $types = TikiLib::lib('trk')->status_types();
                $possibilities = array_map(function ($item) {
                    return $item['label'];
                }, $types);

                $collection = new self($this->definition);
                $collection->addNew($name, 'dropdown')
                    ->setLabel(tr('Status'))
                    ->setControl(new Control\DropDown("tfdd_status", $possibilities))
                    ->setApplyCondition(function ($control, Search_Query $query) {
                        $value = $control->getValue();

                        if ($value) {
                            $query->filterIdentifier($value, 'tracker_status');
                        }
                    });

                $controls = [
                    'multiselect' => new Control\MultiSelect("tfms_status", $possibilities),
                    'checkboxes' => new Control\InlineCheckboxes("tfc_status", $possibilities),
                ];
                foreach ($controls as $key => $control) {
                    $collection->addNew($name, $key)
                    ->setLabel(tr('Status'))
                    ->setControl($control)
                    ->setApplyCondition(function ($control, Search_Query $query) {
                        $values = $control->getValues();

                        if (! empty($values)) {
                            $sub = $query->getSubQuery("tfms_status");

                            foreach ($values as $v) {
                                $sub->filterIdentifier((string) $v, 'tracker_status');
                            }
                        }
                    });
                }

                return $collection;

            case 'modification_date':
                $collection = new self($this->definition);
                $collection->addNew($name, 'range')
                    ->setLabel(tr('Modification Date'))
                    ->setControl(new Control\DateRange("tfdd_modification_date_range"))
                    ->setApplyCondition(function ($control, Search_Query $query) {
                        if ($control->hasValue()) {
                            $query->filterRange($control->getFrom(), $control->getTo(), 'modification_date');
                        }
                    });

                return $collection;

            case 'creation_date':
                $collection = new self($this->definition);
                $collection->addNew($name, 'range')
                    ->setLabel(tr('Creation Date'))
                    ->setControl(new Control\DateRange("tfdd_creation_date_range"))
                    ->setApplyCondition(function ($control, Search_Query $query) {
                        if ($control->hasValue()) {
                            $query->filterRange($control->getFrom(), $control->getTo(), 'creation_date');
                        }
                    });

                return $collection;

            case 'facet':
                $collection = new self($this->definition);

                $lib = \TikiLib::lib('unifiedsearch');
                $provider = $lib->getFacetProvider();

                foreach ($provider->getFacets() as $facet) {
                    $getoptions = function () use ($facet) {
                        if ($this->resultset) {
                            if ($filter = $this->resultset->getFacet($facet)) {
                                return $filter->getOptions();
                            }
                        }

                        return [];
                    };

                    $renderextra = function ($id) use ($facet) {
                        $label = $facet->render($id) ?: tr('Unknown value');
                        return "$label (0)";
                    };

                    $collection->addNew($name, 'facet-any-' . $facet->getName())
                    ->setLabel(tr('%0 (any of)', $facet->getLabel()))
                    ->setControl(new Control\MultiSelect("facet_any_{$facet->getName()}", $getoptions, $renderextra))
                    ->setApplyCondition(function ($control, Search_Query $query) use ($facet) {
                        $query->requestFacet($facet);

                        $values = $control->getValues();
                        if (! empty($values)) {
                            $query->getPostFilter()->filterContent(implode(" OR ", $values), $facet->getName());
                        }
                    });

                    $collection->addNew($name, 'facet-all-' . $facet->getName())
                        ->setLabel(tr('%0 (all of)', $facet->getLabel()))
                        ->setControl(new Control\MultiSelect("facet_all_{$facet->getName()}", $getoptions, $renderextra))
                        ->setApplyCondition(function ($control, Search_Query $query) use ($facet) {
                            $query->requestFacet($facet);

                            $values = $control->getValues();
                            if (! empty($values)) {
                                $query->getPostFilter()->filterContent(implode(" AND ", $values), $facet->getName());
                            }
                        });
                }

                return $collection;
        }
    }

    public function getFilterDescriptor()
    {
        return array_map(function ($filter) {
            return [
                'label' => $filter->getLabel(),
                'field' => $filter->getField(),
                'mode' => $filter->getMode(),
                'position' => $filter->getPosition(),
            ];
        }, $this->filters);
    }

    public function getQueryArguments()
    {
        $parts = [];
        foreach ($this->filters as $filter) {
            $parts += $filter->getControl()->getQueryArguments();
        }

        return $parts;
    }

    public function getAvailableFields()
    {
        $fields = [
            'itemId' => tr('Item ID'),
            'status' => tr('Status'),
            'modification_date' => tr('Modified Date'),
            'creation_date' => tr('Creation Date'),
            'search' => tr('Search'),
            'actions' => tr('Actions'),
            'facet' => tr('Dynamic Filters'),
        ];

        foreach ($this->definition->getFields() as $f) {
            $fields[$f['permName']] = $f['name'];
        }

        return $fields;
    }

    public function setResultSet(\Search_ResultSet $resultset)
    {
        $this->resultset = $resultset;
    }
}