<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class Services_Wiki_Controller
{
    /**
     * Filters for $input->replaceFilters() used in the Services_Utilities()->setVars method
     *
     * @var array
     */
    private $filters = [
        'checked'           => 'pagename',
        'page'              => 'pagename',
        'items'             => 'pagename',
        'version'           => 'alnum',
        'last'              => 'alpha',
        'all'               => 'alpha',
        'create_redirect'   => 'alpha',
        'destpage'          => 'pagename',
    ];

    public function setUp()
    {
        Services_Exception_Disabled::check('feature_wiki');
    }

    /**
     * Returns the section for use with certain features like banning
     * @return string
     */
    public function getSection()
    {
        return 'wiki page';
    }

    /**
     * Returns all accessible wiki pages
     * @param $input
     * @return array
     */
    public function action_pages($input)
    {
        return TikiLib::lib('tiki')->list_pages(
            $input->offset->int(),
            $input->maxRecords->int() ? $input->maxRecords->int() : -1,
            $input->sortMode->text(),
            $input->find->text(),
            $input->initial->text(),
            $input->exactMatch->text(),
            false,
            true,
            $input->onlyOrphans->text() == 'y',
            $input->filter->asArray(),
            $input->onlyCant->text() == 'y'
        );
    }

    /**
     * @param $input
     * @return array
     * @throws Services_Exception_NotFound
     */
    public function action_get_page($input)
    {
        $page = $input->page->text();
        $info = TikiLib::lib('wiki')->get_page_info($page);
        if (! $info) {
            throw new Services_Exception_NotFound(tr('Page "%0" not found', $page));
        }
        $canBeRefreshed = false;
        $data = TikiLib::lib('wiki')->get_parse($page, $canBeRefreshed);
        return ['data' => $data];
    }

    /**
     * Creates or updates a wiki page
     * @param $input
     * @return array
     * @throws Services_Exception
     */
    public function action_create_update_page($input)
    {
        global $user, $prefs, $tiki_p_edit;
        require_once('lib/debug/Tracer.php');

        $tikilib = TikiLib::lib('tiki');

        if ($input->create->int()) {
            $page = $input->pageName->pagename();
            if (empty($page)) {
                throw new Services_Exception(tr('Page name is required.'));
            }
            $perms = Perms::get();
            if (! $perms->edit) {
                throw new Services_Exception_Denied();
            }
        } else {
            $page = $input->page->pagename();
            $info = $tikilib->get_page_info($page);
            if (! $info) {
                throw new Services_Exception_NotFound();
            }
            $tikilib->get_perm_object($page, 'wiki page', $info, true);
            if ($tiki_p_edit !== 'y') {
                throw new Services_Exception_Denied();
            }
        }

        $max_pagename_length = TikiLib::lib('wiki')->max_pagename_length();
        if (strlen($page) > $max_pagename_length) {
            throw new Services_Exception(tr('Page name maximum length of %0 exceeded.', $max_pagename_length));
        }

        $data = $tikilib->convertAbsoluteLinksToRelative($input->data->text());

        if ($input->create->int()) {
            $result = $tikilib->create_page(
                $page,
                0,
                $data,
                $tikilib->now,
                $input->comment->text(),
                $user,
                $tikilib->get_ip_address(),
                $input->description->text(),
                $input->lang->text(),
                $input->is_html->int(),
                [
                    'lock_it' => $input->lock_it->text(),
                    'comments_enabled' => $input->comments_enabled->text(),
                ],
                null,
                $input->wiki_authors_style->text()
            );
        } else {
            $result = $tikilib->update_page(
                $page,
                $data,
                $input->comment->text(),
                $user,
                $tikilib->get_ip_address(),
                $input->description->text(),
                $input->is_minor->text() === 'y',
                $input->lang->text(),
                $input->is_html->int(),
                [
                    'lock_it' => $input->lock_it->text(),
                    'comments_enabled' => $input->comments_enabled->text(),
                ],
                null,
                null,
                $input->wiki_authors_style->text()
            );
        }

        $info = $tikilib->get_page_info($page, true, true);

        if ($info === false || $result === false) {
            $errors = Feedback::errorMessages();
            if ($errors) {
                throw new Services_Exception(implode(' ', $errors));
            }
        }

        if ($prefs['feature_multilingual'] === 'y') {
            $multilinguallib = TikiLib::lib('multilingual');

            // TODO: needs testing
            $translationOf = $input->translationOf->text();
            if (! empty($info['pageLang']) && ! empty($translationOf)) {
                $infoSource = $tikilib->get_page_info($translationOf);
                if ($infoSource) {
                    if (! $exists) {
                        $multilinguallib->insertTranslation('wiki page', $infoSource['page_id'], $infoSource['lang'], $info['page_id'], $info['pageLang']);
                    }
                    $tikilib->cache_page_info = [];
                    if ($input->translationComplete()->text() === 'n') {
                        $multilinguallib->addTranslationInProgressFlags($info['page_id'], $infoSource['lang']);
                    } else {
                        $multilinguallib->propagateTranslationBits(
                            'wiki page',
                            $infoSource['page_id'],
                            $info['page_id'],
                            $infoSource['version'],
                            $info['version']
                        );
                        $multilinguallib->deleteTranslationInProgressFlags($info['page_id'], $infoSource['lang']);
                    }
                }
            } else {
                $multilinguallib->createTranslationBit('wiki page', $info['page_id'], $info['version']);
            }
        }

        if (! empty($prefs['geo_locate_wiki']) && $prefs['geo_locate_wiki'] == 'y' && $input->geolocation->text()) {
            TikiLib::lib('geo')->set_coordinates('wiki page', $page, $input->geolocation->text());
        }

        if (isset($input['page_auto_toc'])) {
            $isAutoTocActive = $input->page_auto_toc->text() === 'y' ? 1 : null;
            TikiLib::lib('wiki')->set_page_auto_toc($page, $isAutoTocActive);
        }

        if ($prefs['wiki_page_hide_title'] == 'y' && isset($input['page_hide_title'])) {
            $isHideTitle = $input->page_hide_title->text() === 'y' ? 1 : null;
            TikiLib::lib('wiki')->set_page_hide_title($page, $isHideTitle);
        }

        if ($prefs['namespace_enabled'] == 'y' && isset($input['explicit_namespace'])) {
            TikiLib::lib('wiki')->set_explicit_namespace($page, $input->explicit_namespace->text());
        }

        return ['info' => $info];
    }

    /**
     * @param $input
     * @return array
     */
    public function action_regenerate_slugs($input)
    {
        global $prefs;
        Services_Exception_Denied::checkGlobal('admin');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $pages = TikiDb::get()->table('tiki_pages');

            $initial = TikiLib::lib('slugmanager');
            $tracker = new Tiki\Wiki\SlugManager\InMemoryTracker();
            $manager = clone $initial;
            $manager->setValidationCallback($tracker);

            $list = $pages->fetchColumn('pageName', []);
            $pages->updateMultiple(['pageSlug' => null], []);

            foreach ($list as $page) {
                $slug = $manager->generate($prefs['wiki_url_scheme'], $page, $prefs['url_only_ascii'] === 'y');

                $count = 1;
                while ($pages->fetchCount(['pageSlug' => $slug]) && $count < 100) {
                    $count++;
                    $slug = $manager->generate($prefs['wiki_url_scheme'], $page . ' ' . $count, $prefs['url_only_ascii'] === 'y');
                }

                $tracker->add($page);
                $pages->update(['pageSlug' => $slug], ['pageName' => $page]);
            }

            TikiLib::lib('access')->redirect('tiki-admin.php?page=wiki');
        }

        return [
            'title' => tr('Regenerate Wiki URLs'),
        ];
    }

    /**
     * List pages "perform with checked" but with no action selected
     *
     * @throws Services_Exception
     */
    public function action_no_action()
    {
        Services_Utilities::modalException(tra('No action was selected. Please select an action before clicking OK.'));
    }


    /**
     * Remove pages action, either all versions (from tiki-listpages.php checkbox action) or last version
     * (page remove button or remove action for an individual page in page listing)
     *
     * @param $input
     * @return array
     * @throws Exception
     * @throws Services_Exception
     */
    public function action_remove_pages($input)
    {
        global $user;
        $util = new Services_Utilities();
        //first pass - show confirm modal popup
        if ($util->notConfirmPost()) {
            $util->setVars($input, $this->filters, 'checked');
            $pages = array_map(function ($pageName) {
                return ['pageName' => $pageName];
            }, $util->items);
            $pages = Perms::simpleFilter('wiki page', 'pageName', 'remove', $pages);
            $util->items = array_map(function ($pageName) {
                return array_pop($pageName);
            }, $pages);
            if (count($util->items) > 0) {
                $v = $input['version'];
                if (count($util->items) == 1) {
                    $versions = TikiLib::lib('hist')->get_nb_history($util->items[0]);
                    $one = $versions == 1;
                } else {
                    $one = false;
                }
                $pdesc = count($util->items) === 1 ? tr('page') : tr('pages');
                if ($one) {
                    $vdesc = tr('the only version of');
                } elseif ($v === 'all') {
                    $vdesc = tr('all versions of');
                } elseif ($v === 'last') {
                    $vdesc = tr('the last version of');
                }
                $msg = tr('Delete %0 the following %1?', $vdesc, $pdesc);
                $included_by = [];
                $wikilib = TikiLib::lib('wiki');
                foreach ($util->items as $page) {
                    $included_by = array_merge($included_by, $wikilib->get_external_includes($page));
                }
                if (sizeof($included_by) == 0) {
                    $included_by = null;
                }
                return [
                    'title' => tra('Please confirm'),
                    'confirmAction' => $input['action'],
                    'confirmController' => 'wiki',
                    'customMsg' => $msg,
                    'confirmButton' => tra('Delete'),
                    'items' => $util->items,
                    'extra' => ['referer' => Services_Utilities::noJsPath(), 'version' => $v, 'one' => $one],
                    'modal' => '1',
                    'included_by' => $included_by,
                ];
            } else {
                if (count($util->items) > 0) {
                    Services_Utilities::modalException(tra('You do not have permission to remove the selected page(s)'));
                } else {
                    Services_Utilities::modalException(tra('No pages were selected. Please select one or more pages.'));
                }
            }
            //after confirm submit - perform action and return success feedback
        } elseif ($util->checkCsrf()) {
            $util->setVars($input, $this->filters, 'items');
            $pages = array_map(function ($pageName) {
                return ['pageName' => $pageName];
            }, $util->items);
            $pages = Perms::simpleFilter('wiki page', 'pageName', 'remove', $pages);
            $util->items = array_map(function ($pageName) {
                return array_pop($pageName);
            }, $pages);
            //delete page
            //checkbox in popup where user can change from all to last and vice versa
            $all = ! empty($input['all']) && $input['all'] === 'on';
            $last = ! empty($input['last']) && $input['last'] === 'on';
            //only use default when not overriden by checkbox
            $all = $all || ($util->extra['version'] === 'all' && ! $last);
            $last = $last || ($util->extra['version'] === 'last' && ! $all);
            $error = false;
            foreach ($util->items as $page) {
                $result = false;
                //get page info before deletion in case this was the page the user was on
                //used later to redirect to the tiki index page
                $allinfo = TikiLib::lib('tiki')->get_page_info($page, false, true);
                $history = false;
                if ($all || $util->extra['one']) {
                    $result = TikiLib::lib('tiki')->remove_all_versions($page);
                } elseif ($last) {
                    $result = TikiLib::lib('wiki')->remove_last_version($page);
                } elseif (! empty($util->extra['version']) && is_numeric($util->extra['version'])) {
                    $result = TikiLib::lib('hist')->remove_version($page, $util->extra['version']);
                    $history = true;
                }
                if (! $result) {
                    $error = true;
                    $versionText = $history ? tr('Version') . ' ' : '';
                    $feedback = [
                        'tpl' => 'action',
                        'mes' => tr('An error occurred. %0%1 could not be deleted.', $versionText, $page),
                    ];
                    Feedback::error($feedback);
                }
            }
            // Clear cache in order to update menus and structures
            $cachelib = TikiLib::lib('cache');
            $cachelib->empty_type_cache('menu');
            $cachelib->empty_type_cache('structure');
            //prepare feedback
            if (! $error) {
                if ($all || $util->extra['one']) {
                    $vdesc = tr('All versions');
                    $verb = 'have';
                    $noversionsleft = true;
                } elseif ($last) {
                    $vdesc = tr('The last version');
                    $verb = 'has';
                } else {
                    //must be a version number
                    $vdesc = tr('Version %0', $util->extra['version']);
                    $verb = 'has';
                }
                if (count($util->items) === 1) {
                    $msg = tr('%0 of the following page %1 been deleted:', $vdesc, $verb);
                } else {
                    $msg = tr('%0 of the following pages %1 been deleted:', $vdesc, $verb);
                }
                $feedback = [
                    'tpl' => 'action',
                    'mes' => $msg,
                    'items' => $util->items,
                ];
                Feedback::success($feedback);
                // Create a Semantic Alias (301 redirect) if this option was selected by user.
                $createredirect = ! empty($input['create_redirect']) && $input['create_redirect'] === 'y';
                if ($createredirect && $noversionsleft) {
                    $destinationPage = $input['destpage'];
                    if ($destinationPage == "") {
                        $msg = tr('Redirection page not specified. 301 redirect not created.');
                        $feedback = [
                            'tpl' => 'action',
                            'mes' => $msg
                        ];
                        Feedback::warning($feedback);
                    } else {
                        $appendString = "";
                        foreach ($util->items as $page) {
                            // Append on the destination page's content the following string,
                            // where $page is the name of the deleted page:
                            // "\r\n~tc~(alias($page))~/tc~"
                            // We use the ~tc~ so that it doesn't make the destination page look ugly
                            if (count($util->items) > 1) {
                                $comment = tr('Semantic aliases (301 Redirects) to this page were created when other pages were deleted');
                            } else {
                                $comment = tr('A semantic alias (301 Redirect) to this page was created when page %0 was deleted', $page);
                            }
                            $appendString .= "\r\n~tc~ (alias($page)) ~/tc~";
                        }
                        if (TikiLib::lib('tiki')->page_exists($destinationPage)) {
                            // Get wiki page content
                            $infoDestinationPage = TikiLib::lib('tiki')->get_page_info($destinationPage);
                            $page_data = $infoDestinationPage['data'];
                            $page_data .= $appendString;
                            TikiLib::lib('tiki')->update_page($destinationPage, $page_data, $comment, $user, TikiLib::lib('tiki')->get_ip_address());
                            if (count($util->items) > 1) {
                                $msg = tr('301 Redirects to the following page were created:');
                            } else {
                                $msg = tr('A 301 Redirect to the following page was created:');
                            }
                        } else {
                            if (count($util->items) > 1) {
                                $page_data = tr("THIS PAGE WAS CREATED AUTOMATICALLY when other pages were removed. Please edit and write the definitive contents.");
                            } else {
                                $page_data = tr("THIS PAGE WAS CREATED AUTOMATICALLY when another page was removed. Please edit and write the definitive contents.");
                            }
                            $page_data .= $appendString;
                            // Create a new page
                            TikiLib::lib('tiki')->create_page($destinationPage, 0, $page_data, TikiLib::lib('tiki')->now, $comment, $user, TikiLib::lib('tiki')->get_ip_address())