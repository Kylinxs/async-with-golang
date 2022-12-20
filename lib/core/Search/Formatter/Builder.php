
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Search_Formatter_Builder
{
    private $parser;
    private $paginationArguments;

    private $formatterPlugin;
    private $subFormatters = [];
    private $customFilters = [];
    private $alternateOutput;
    private $id;
    private $count;
    private $tsOn;
    private $tsettings;
    private $actions;
    private $isDownload;
    private $downloadName;

    public function __construct()
    {
        $this->parser = new WikiParser_PluginArgumentParser();
        $this->paginationArguments = [
            'offset_arg' => 'offset',
            'max' => 50,
        ];
        $this->actions = [];
        $this->isDownload = false;
    }

    public function setPaginationArguments($arguments)
    {
        $this->paginationArguments = $arguments;
    }

    public function setFormatterPlugin(Search_Formatter_Plugin_Interface $plugin)
    {
        $this->formatterPlugin = $plugin;
    }

    public function setActions($actions)
    {
        $this->actions = $actions;
    }

    public function setDownload($isDownload)
    {
        $this->isDownload = $isDownload;
    }

    public function apply($matches)
    {
        foreach ($matches as $match) {
            $name = $match->getName();

            if ($name == 'output') {
                $this->handleOutput($match);
            }

            if ($name == 'format') {
                $this->handleFormat($match);
            }

            if ($name == 'alternate') {
                $this->handleAlternate($match);
            }

            if ($name == 'tablesorter') {
                $this->handleTablesorter($match);
            }

            if ($name == 'filter') {
                $this->handleFilter($match);
            }
        }
    }

    public function getFormatter($errorInQuery = false)
    {
        global $prefs;
        $plugin = $this->formatterPlugin;
        if (! $plugin) {
            $plugin = new Search_Formatter_Plugin_WikiTemplate("* {display name=title format=objectlink}\n");
        }

        $formatter = Search_Formatter_Factory::newFormatter($plugin);

        if ($this->alternateOutput > '') {
            $formatter->setAlternateOutput($this->alternateOutput);
        } else {
            $formatter->setAlternateOutput('^' . tra('No results for query.') . '^');
            if ($errorInQuery) {
                $formatter->setAlternateOutput('^' . tra('There is a query error in the sort control block, please correct it to find results.') . '^');
            }
        }

        foreach ($this->subFormatters as $name => $plugin) {
            $formatter->addSubFormatter($name, $plugin);
        }

        foreach ($this->customFilters as $filter) {
            $formatter->addCustomFilter($filter);
        }

        return $formatter;
    }

    private function handleFormat($match)
    {
        $arguments = $this->parser->parse($match->getArguments());

        if (isset($arguments['mode']) && $arguments['mode'] == 'download' && ! $this->isDownload) {
            return;
        }

        if (isset($arguments['name'])) {
            $plugin = new Search_Formatter_Plugin_WikiTemplate($match->getBody());
            $plugin->setRaw(! empty($arguments['mode']) && $arguments['mode'] == 'raw');
            $this->subFormatters[$arguments['name']] = $plugin;
        }
    }

    private function handleFilter($match)
    {
        $arguments = $this->parser->parse($match->getArguments());

        if (! isset($arguments['editable'], $arguments['field'])) {
            return;
        }

        if (
            in_array($arguments['field'], array_map(function ($f) {
                return $f['field'];
            }, $this->customFilters))
        ) {
            return;
        }

        $this->customFilters[] = [
            'field' => $arguments['field'],
            'mode' => $arguments['editable']
        ];
    }

    private function handleAlternate($match)
    {
        $this->alternateOutput = $match->getBody();
    }

    /**
     * @param WikiParser_PluginMatcher_Match $output
     *
     * @throws Exception
     */
    private function handleOutput($output)
    {
        $smarty = TikiLib::lib('smarty');
        $tikilib = TikiLib::lib('tiki');
        $arguments = $this->parser->parse($output->getArguments());

        if (isset($arguments['template'])) {
            if ($arguments['template'] == 'table') {
                $arguments['template'] = __DIR__ . '/../../../../templates/search/list/table.tpl';
                $arguments['pagination'] = true;
            } elseif ($arguments['template'] == 'medialist') {
                $arguments['template'] = __DIR__ . '/../../../../templates/search/list/medialist.tpl';
            } elseif ($arguments['template'] == 'carousel') {
                $arguments['template'] = __DIR__ . '/../../../../templates/search/list/carousel.tpl';
            } elseif ($arguments['template'] == 'count') {
                $arguments['template'] = __DIR__ . '/../../../../templates/search/list/count.tpl';
            } elseif ($arguments['template'] == 'debug') {
                $arguments['template'] = __DIR__ . '/../../../../templates/search/list/debug.tpl';
            } elseif ($arguments['template'] == 'json') {
                $arguments['template'] = __DIR__ . '/../../../../templates/search/list/json_encode.tpl';
            } elseif (! file_exists($arguments['template'])) {
                $temp = $smarty->get_filename($arguments['template']);
                if (empty($temp)) { //if get_filename cannot find template, return error
                    Feedback::error(tr('Missing template "%0"', $arguments['template']));
                    return '';
                }
                $arguments['template'] = $temp;
            }
            $abuilder = new Search_Formatter_ArrayBuilder();
            $outputData = $abuilder->getData($output->getBody());
            foreach ($this->paginationArguments as $k => $v) {
                $outputData[$k] = $this->paginationArguments[$k];
            }
            if (strstr($arguments['template'], 'table')) {
                $outputData['actions'] = $this->actions;
                if (isset($arguments['downloadable'])) {
                    $outputData['downloadable'] = true;
                    $this->downloadName = $arguments['downloadable'];
                    if (isset($arguments['downloadable-position'])) {
                        if ($arguments['downloadable-position'] == 'top') {
                            $outputData['downloadabletop'] = 'y';
                        } elseif ($arguments['downloadable-position'] == 'both') {
                            $outputData['downloadabletop'] = 'y';
                            $outputData['downloadablebottom'] = 'y';
                        } else {    // Either value is "bottom" or value is not recognised
                            $outputData['downloadablebottom'] = 'y';
                        }
                    } else {    // Default is link at the bottom for compatibility
                        $outputData['downloadablebottom'] = 'y';
                    }
                }
                if ($this->isDownload) {
                    $this->formatterPlugin = new Search_Formatter_Plugin_CsvTemplate($output->getBody());
                    return;
                }
            }

            $templateData = file_get_contents($arguments['template']);

            $plugin = new Search_Formatter_Plugin_SmartyTemplate($arguments['template']);
            $plugin->setData($outputData);
            $plugin->setFields($this->findFields($outputData, $templateData));
        } elseif (isset($arguments['tplwiki'])) {
            if ($tikilib->page_exists($arguments['tplwiki'])) {
                $wikitpl = "tplwiki:" . $arguments['tplwiki'];
                $abuilder = new Search_Formatter_ArrayBuilder();
                $outputData = $abuilder->getData($output->getBody());
                foreach ($this->paginationArguments as $k => $v) {
                    $outputData[$k] = $this->paginationArguments[$k];
                }
                $data = $tikilib->get_page_info($arguments['tplwiki']);
                $wikicontent = $data['data'];
                $plugin = new Search_Formatter_Plugin_SmartyTemplate($wikitpl);
                $plugin->setData($outputData);
                $plugin->setFields($this->findFields($outputData, $wikicontent));
            } else {
                Feedback::error(tr('Template tplwiki page "%0" not found', $arguments['tplwiki']));
            }
        } elseif (isset($arguments['wiki'])) {
            if ($tikilib->page_exists($arguments['wiki'])) {
                $wikitpl = "tplwiki:" . $arguments['wiki'];
                $wikicontent = $smarty->fetch($wikitpl);
                $plugin = new Search_Formatter_Plugin_WikiTemplate($wikicontent);
            } else {
                Feedback::error(tr('Template wiki page "%0" not found', $arguments['wiki']));
            }
        } elseif (isset($arguments['report'])) {
            $plugin = new Search_Formatter_Plugin_ReportTemplate($output->getBody());
        } else {
            $plugin = new Search_Formatter_Plugin_WikiTemplate($output->getBody());
        }

        if (isset($arguments['pagination'])) {
            $plugin = new Search_Formatter_AppendPagination($plugin, $this->paginationArguments);
        }

        $this->formatterPlugin = $plugin;
    }

    private function handleTablesorter($match)
    {
        $args = $this->parser->parse($match->getArguments());
        if (! $this->tsOn) {
            return false;
        }
        if (! Table_Check::isAjaxCall()) {
            $ts = new Table_Plugin();
            $ts->setSettings(
                $this->id,
                isset($args['server']) ? $args['server'] : 'n',
                isset($args['sortable']) ? $args['sortable'] : 'y',
                isset($args['sortList']) ? $args['sortList'] : null,
                isset($args['tsortcolumns']) ? $args['tsortcolumns'] : null,
                isset($args['tsfilters']) ? $args['tsfilters'] : null,
                isset($args['tsfilteroptions']) ? $args['tsfilteroptions'] : null,
                isset($args['tspaginate']) ? $args['tspaginate'] : null,
                isset($args['tscolselect']) ? $args['tscolselect'] : null,
                $GLOBALS['requestUri'],
                $this->count,
                isset($args['tstotals']) ? $args['tstotals'] : null,
                isset($args['tstotalformat']) ? $args['tstotalformat'] : null,
                isset($args['tstotaloptions']) ? $args['tstotaloptions'] : null,
                isset($args['showProcessing']) ? $args['showProcessing'] : null,
                isset($args['ignoreCase']) ? $args['ignoreCase'] : null,
                isset($args['sortLocaleCompare']) ? $args['sortLocaleCompare'] : null,
                isset($args['tsoutput']) ? $args['tsoutput'] : null
            );
            if (is_array($ts->settings)) {
                $ts->settings['ajax']['offset'] = 'offset';
                Table_Factory::build('PluginWithAjax', $ts->settings);
                $this->setTsSettings($ts->settings);
            }
        }
    }

    private function findFields($outputData, $templateData)
    {
        $outputData = TikiLib::array_flat($outputData);

        // Heuristic based: only lowercase letters, digits and underscore
        $fields = [];
        foreach ($outputData as $candidate) {
            if (! is_string($candidate)) {
                continue;
            }
            if (preg_match("/^[a-z0-9_]+$/", $candidate) || substr($candidate, 0, strlen('tracker_field_')) === 'tracker_field_') {
                $fields[] = $candidate;
            }
        }

        preg_match_all('/\$(result|row|res)\.([a-z0-9_]+)[\|\}\w]+/', $templateData, $matches);
        $fields = array_merge($fields, $matches[2]);

        $fields = array_fill_keys(array_unique($fields), null);

        return $fields;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setCount($count)
    {
        $this->count = $count;
    }

    public function setTsOn($tsOn)
    {
        $this->tsOn = $tsOn;
    }

    private function setTsSettings($tsettings)
    {
        $this->tsettings = $tsettings;
    }

    public function getTsSettings()
    {
        return $this->tsettings;
    }

    public function getDownloadName()
    {
        return $this->downloadName;
    }
}