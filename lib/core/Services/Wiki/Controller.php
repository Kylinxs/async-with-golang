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
                            TikiLib::lib('tiki')->create_page($destinationPage, 0, $page_data, TikiLib::lib('tiki')->now, $comment, $user, TikiLib::lib('tiki')->get_ip_address());
                            if (count($util->items) > 1) {
                                $msg = tr('The following page and 301 Redirects to it were created:');
                            } else {
                                $msg = tr('The following page and a 301 Redirect to it were created:');
                            }
                        }
                        $feedback = [
                            'tpl' => 'link',
                            'mes' => $msg,
                            'items' => $destinationPage,
                        ];
                        Feedback::note($feedback);
                    }
                }
            }
            //return to page
            if (
                count($util->items) === 1 && ($all || $util->extra['one'])
                && strpos($_SERVER['HTTP_REFERER'], $allinfo['pageName']) !== false
            ) {
                //go to tiki index if the page the user was on has been deleted - avoids no page found error.
                global $prefs, $base_url;
                return Services_Utilities::redirect($base_url . $prefs['tikiIndex']);
            }
            return Services_Utilities::refresh($util->extra['referer']);
        }
    }

    /**
     * Remove page versions action on the tiki-pagehistory.php page
     *
     * @param $input
     * @return array
     * @throws Exception
     * @throws Services_Exception
     * @throws Services_Exception_Denied
     */
    public function action_remove_page_versions($input)
    {
        $util = new Services_Utilities();
        //first pass - show confirm modal popup
        if ($util->notConfirmPost()) {
            $util->setVars($input, $this->filters, 'checked');
            $p = $input['page'];
            Services_Exception_Denied::checkObject('remove', 'wiki page', $p);
            if ($util->itemsCount > 0) {
                $vdesc = count($util->items) === 1 ? 'version' : 'versions';
                $msg = tr('Delete the following %0 of %1?', $vdesc, $p);
                return $util->confirm($msg, tra('Delete'), ['page' => $p]);
            } else {
                Services_Utilities::modalException(tra('No version were selected. Please select one or more versions.'));
            }
            //after confirm submit - perform action and return success feedback
        } elseif ($util->checkCsrf()) {
            $util->setVars($input, $this->filters, 'items');
            Services_Exception_Denied::checkObject('remove', 'wiki page', $util->extra['page']);
            //delete page
            $histlib = TikiLib::lib('hist');
            $pageinfo = TikiLib::lib('tiki')->get_page_info($util->extra['page']);
            $error = false;
            if ($pageinfo['flag'] != 'L') {
                $result = false;
                foreach ($util->items as $version) {
                    $result = $histlib->remove_version($util->extra['page'], $version);
                }
                if (! $result) {
                    $error = true;
                    $feedback = [
                        'tpl' => 'action',
                        'mes' => tr('An error occurred. Version %0 could not be deleted.', $version),
                    ];
                    Feedback::error($feedback);
                }
            }
            if (! $error) {
                //prepare feedback
                if ($util->itemsCount === 1) {
                    $msg = tr('The following version of %0 has been deleted:', $util->extra['page']);
                } else {
                    $msg = tr('The following versions of %0 have been deleted:', $util->extra['page']);
                }
                $feedback = [
                    'tpl' => 'action',
                    'mes' => $msg,
                    'items' => $util->items,
                ];
                Feedback::success($feedback);
            }
            //return to page
            return Services_Utilities::refresh($util->extra['referer']);
        }
    }

    /**
     * Listpages "perform with checked" action to print pages
     *
     * @param $input
     * @return array
     * @throws Exception
     * @throws Services_Exception
     * @throws Services_Exception_Disabled
     */
    public function action_print_pages($input)
    {
        Services_Exception_Disabled::check('feature_wiki_multiprint');
        $util = new Services_Utilities();
        //first pass - show confirm modal popup
        if ($util->notConfirmPost()) {
            $util->setVars($input, $this->filters, 'checked');
            $pages = array_map(function ($pageName) {
                return ['pageName' => $pageName];
            }, $util->items);
            $pages = Perms::simpleFilter('wiki page', 'pageName', 'view', $pages);
            $util->items = array_map(function ($pageName) {
                return array_pop($pageName);
            }, $pages);
            if (count($util->items) > 0) {
                if (count($util->items) === 1) {
                    $msg = tr('Print the following page?');
                } else {
                    $msg = tr('Print the following pages?');
                }
                return $util->confirm($msg, tra('Print'));
            } else {
                Services_Utilities::modalException(tra('No pages were selected. Please select one or more pages.'));
            }
        //after confirm submit - perform action and return success feedback
        } elseif ($util->checkCsrf()) {
            $util->setVars($input, $this->filters, 'items');
            $pages = array_map(function ($pageName) {
                return ['pageName' => $pageName];
            }, $util->items);
            $pages = Perms::simpleFilter('wiki page', 'pageName', 'view', $pages);
            $util->items = array_map(function ($pageName) {
                return array_pop($pageName);
            }, $pages);
            if (! empty($util->items)) {
                return ['url' => 'tiki-print_multi_pages.php?print=y&printpages=' . urlencode(json_encode($util->items))];
            } else {
                Feedback::error(tr('No page specified.'));
                return Services_Utilities::refresh($util->extra['referer']);
            }
        }
    }

    public function action_export_pdf($input)
    {
        Services_Exception_Disabled::check('feature_wiki_multiprint');
        $util = new Services_Utilities();
        //first pass - show confirm modal popup
        if ($util->notConfirmPost()) {
            $util->setVars($input, $this->filters, 'checked');
            $pages = array_map(function ($pageName) {
                return ['pageName' => $pageName];
            }, $util->items);
            $pages = Perms::simpleFilter('wiki page', 'pageName', 'view', $pages);
            $util->items = array_map(function ($pageName) {
                return array_pop($pageName);
            }, $pages);
            if (count($util->items) > 0) {
                include_once 'lib/pdflib.php';
                $pdf = new PdfGenerator();
                if (! empty($pdf->error)) {
                    Services_Utilities::modalException($pdf->error);
                } else {
                    if (count($util->items) === 1) {
                        $msg = tr('Export the following page to PDF?');
                    } else {
                        $msg = tr('Export the following pages to PDF?');
                    }
                    return $util->confirm($msg, tra('PDF'));
                }
            } else {
                Services_Utilities::modalException(tra('No pages were selected. Please select one or more pages.'));
            }
        //after confirm submit - perform action
        } elseif ($util->checkCsrf()) {
            $util->setVars($input, $this->filters, 'items');
            $pages = array_map(function ($pageName) {
                return ['pageName' => $pageName];
            }, $util->items);
            $pages = Perms::simpleFilter('wiki page', 'pageName', 'view', $pages);
            $util->items = array_map(function ($pageName) {
                return array_pop($pageName);
            }, $pages);
            if (! empty($util->items)) {
                include_once 'lib/pdflib.php';
                $pdf = new PdfGenerator();
                if (empty($pdf->error)) {
                    return ['url' => 'tiki-print_multi_pages.php?display=pdf&printpages='
                        . urlencode(json_encode($util->items))];
                } else {
                    Feedback::error($pdf->error);
                }
            } else {
                Feedback::error(tr('No page specified.'));
            }
            return Services_Utilities::closeModal($util->extra['referer']);
        }
    }

    /**
     * Listpages "perform with checked" action to lock pages
     *
     * @param $input
     * @return array
     * @throws Exception
     * @throws Services_Exception
     * @throws Services_Exception_Disabled
     */
    public function action_lock_pages($input)
    {
        Services_Exception_Disabled::check('feature_wiki_usrlock');
        $util = new Services_Utilities();
        //first pass - show confirm modal popup
        if ($util->notConfirmPost()) {
            $util->setVars($input, $this->filters, 'checked');
            $countUnfiltered = count($util->items);
            $pages = array_map(function ($pageName) {
                return ['pageName' => $pageName];
            }, $util->items);
            $pages = Perms::simpleFilter('wiki page', 'pageName', 'view', $pages);
            $util->items = array_map(function ($pageName) {
                return array_pop($pageName);
            }, $pages);
            foreach ($util->items as $key => $page) {
                if (TikiLib::lib('wiki')->is_locked($page)) {
                    unset($util->items[$key]);
                }
            }
            if (count($util->items) > 0) {
                if (count($util->items) === 1) {
                    $msg = tr('Lock the following page?');
                } else {
                    $msg = tr('Lock the following pages?');
                }
                $ret = $util->confirm($msg, tra('Lock'));
                if ($countUnfiltered > count($util->items)) {
                    $ret['FORWARD']['help'] = tr('Excludes selected pages already locked or for which you lack permission to lock.');
                }
                return $ret;
            } else {
                if ($countUnfiltered > count($util->items)) {
                    Services_Utilities::modalException(tra('You do not have permission to lock the selected pages or they have already been locked.'));
                } else {
                    Services_Utilities::modalException(tra('No pages were selected. Please select one or more pages.'));
                }
            }
        //after confirm submit - perform action
        } elseif ($util->checkCsrf()) {
            $util->setVars($input, $this->filters, 'items');
            $pages = array_map(function ($pageName) {
                return ['pageName' => $pageName];
            }, $util->items);
            $pages = Perms::simpleFilter('wiki page', 'pageName', 'view', $pages);
            $util->items = array_map(function ($pageName) {
                return array_pop($pageName);
            }, $pages);
            $errorpages = [];
            foreach ($util->items as $page) {
                $res = TikiLib::lib('wiki')->lock_page($page);
                if (! $res) {
                    $errorpages[] = $page;
                }
            }
            $locked = array_diff($util->items, $errorpages);
            //prepare and send feedback
            if (count($errorpages) > 0) {
                if (count($errorpages) === 1) {
                    $msg1 = tr('The following page was not locked due to an error:');
                } else {
                    $msg1 = tr('The following pages were not locked due to an error:');
                }
                $feedback1 = [
                    'tpl' => 'action',
                    'mes' => $msg1,
                    'items' => $errorpages,
                ];
                Feedback::error($feedback1);
            }
            if (count($locked) > 0) {
                if (count($locked) === 1) {
                    $msg2 = tr('The following page has been locked:');
                } else {
                    $msg2 = tr('The following pages have been locked:');
                }
                $feedback2 = [
                    'tpl' => 'action',
                    'mes' => $msg2,
                    'items' => $locked,
                ];
                Feedback::success($feedback2);
            }
            //return to page
            return Services_Utilities::refresh($util->extra['referer']);
        }
    }

    /**
     * Listpages "perform with checked" action to unlock pages
     *
     * @param $input
     * @return array
     * @throws Exception
     * @throws Services_Exception
     * @throws Services_Exception_Disabled
     */
    public function action_unlock_pages($input)
    {
        Services_Exception_Disabled::check('feature_wiki_usrlock');
        $util = new Services_Utilities();
        //first pass - show confirm modal popup
        if ($util->notConfirmPost()) {
            $util->setVars($input, $this->filters, 'checked');
            $countUnfiltered = $util->itemsCount;
            $admin = Perms::get()->admin_wiki;
            global $user;
            foreach ($util->items as $key => $page) {
                $pinfo = TikiLib::lib('tiki')->get_page_info($page);
                if (
                    ! ($pinfo['flag'] == 'L' &&
                    ($admin || ($user == $pinfo['lockedby']) ||
                    (! $pinfo['lockedby'] && $user == $pinfo['user'])))
                ) {
                    unset($util->items[$key]);
                }
            }
            if (count($util->items) > 0) {
                if (count($util->items) === 1) {
                    $msg = tr('Unlock the following page?');
                } else {
                    $msg = tr('Unlock the following pages?');
                }
                $ret = $util->confirm($msg, tra('Unlock'));
                if ($countUnfiltered > count($util->items)) {
                    $ret['FORWARD']['help'] = tr('Excludes selected pages already unlocked or for which you lack permission to unlock.');
                }
                return $ret;
            } else {
                if ($countUnfiltered > count($util->items)) {
                    Services_Utilities::modalException(tra('You do not have permission to unlock the selected pages or they have already been unlocked.'));
                } else {
                    Services_Utilities::modalException(tra('No pages were selected. Please select one or more pages.'));
                }
            }
            //after confirm submit - perform action
        } elseif ($util->checkCsrf()) {
            $util->setVars($input, $this->filters, 'items');
            $admin = Perms::get()->admin_wiki;
            global $user;
            foreach ($util->items as $key => $page) {
                $pinfo = TikiLib::lib('tiki')->get_page_info($page);
                if (
                    ! ($pinfo['flag'] == 'L' &&
                    ($admin || ($user == $pinfo['lockedby']) ||
                    (! $pinfo['lockedby'] && $user == $pinfo['user'])))
                ) {
                    unset($util->items[$key]);
                }
            }
            $errorpages = [];
            foreach ($util->items as $page) {
                $res = TikiLib::lib('wiki')->unlock_page($page);
                if (! $res) {
                    $errorpages[] = $page;
                }
            }
            $locked = array_diff($util->items, $errorpages);
            //prepare and send feedback
            if (count($errorpages) > 0) {
                if (count($errorpages) === 1) {
                    $msg1 = tr('The following page was not unlocked due to an error:');
                } else {
                    $msg1 = tr('The following pages were not unlocked due to an error:');
                }
                $feedback1 = [
                    'tpl' => 'action',
                    'mes' => $msg1,
                    'items' => $errorpages,
                ];
                Feedback::error($feedback1);
            }
            if (count($locked) > 0) {
                if (count($locked) === 1) {
                    $msg2 = tr('The following page has been unlocked:');
                } else {
                    $msg2 = tr('The following pages have been unlocked:');
                }
                $feedback2 = [
                    'tpl' => 'action',
                    'mes' => $msg2,
                    'items' => $locked,
                ];
                Feedback::success($feedback2);
            }
            //return to page
            return Services_Utilities::refresh($util->extra['referer']);
        }
    }

    /**
     * Listpages "perform with checked" action to zip pages
     *
     * @param $input
     * @return array
     * @throws Exception
     * @throws Services_Exception
     * @throws Services_Exception_Denied
     */
    public function action_zip($input)
    {
        Services_Exception_Denied::checkGlobal('admin');
        $util = new Services_Utilities();
        //first pass - show confirm modal popup
        if ($util->notConfirmPost()) {
            $util->setVars($input, $this->filters, 'checked');
            if ($util->itemsCount > 0) {
                if ($util->itemsCount === 1) {
                    $msg = tr('Download a zipped file of the following page?');
                } else {
                    $msg = tr('Download a zipped file of the following pages?');
                }
                return $util->confirm($msg, tra('Zip'));
            } else {
                Services_Utilities::modalException(tra('No pages were selected. Please select one or more pages.'));
            }
        //after confirm submit - perform action
        } elseif ($util->checkCsrf()) {
            $util->setVars($input, $this->filters, 'items');
            include_once('lib/wiki/xmllib.php');
            $xmllib = new XmlLib();
            $zipFile = 'dump/xml.zip';
            $config['debug'] = false;
            if ($xmllib->export_pages($util->items, null, $zipFile, $config)) {
                if (! $config['debug']) {
                    global $base_url;
                    return ['url' => $base_url . $zipFile];
                }
            } else {
                Feedback::error(['mes' => $xmllib->get_error()]);
            }
            //return to page
            return Services_Utilities::closeModal($util->extra['referer']);
        }
    }

    /**
     * Listpages "perform with checked" action to add page name as title to pages
     *
     * @param $input
     * @return array
     * @throws Exception
     * @throws Services_Exception
     * @throws Services_Exception_Denied
     */
    public function action_title($input)
    {
        Services_Exception_Denied::checkGlobal('admin');
        $util = new Services_Utilities();
        //first pass - show confirm modal popup
        if ($util->notConfirmPost()) {
            $util->setVars($input, $this->filters, 'checked');
            if ($util->itemsCount > 0) {
                if ($util->itemsCount === 1) {
                    $msg = tr('Add page name as header of the following page?');
                } else {
                    $msg = tr('Add page name as header of the following pages?');
                }
                return $util->confirm($msg, tra('Add'));
            } else {
                Services_Utilities::modalException(tra('No pages were selected. Please select one or more pages.'));
            }
        //after confirm submit - perform action
        } elseif ($util->checkCsrf()) {
            $util->setVars($input, $this->filters, 'items');
            $errorpages = [];
            foreach ($util->items as $page) {
                $pageinfo = TikiLib::lib('tiki')->get_page_info($page);
                if ($pageinfo) {
                    $pageinfo['data'] = "!$page\r\n" . $pageinfo['data'];
                    $table = TikiLib::lib('tiki')->table('tiki_pages');
                    $table->update(['data' => $pageinfo['data']], ['page_id' => $pageinfo['page_id']]);
                } else {
                    $errorpages[] = $page;
                }
            }
            if (count($errorpages) > 0) {
                if (count($errorpages) === 1) {
                    $msg1 = tr('The following page was not found:');
                } else {
                    $msg1 = tr('The following pages were not found:');
                }
                $feedback1 = [
                    'tpl' => 'action',
                    'mes' => $msg1,
                    'items' => $errorpages,
                ];
                Feedback::error($feedback1);
            }
            $fitems = array_diff($util->items, $errorpages);
            if (count($fitems) > 0) {
                if (count($fitems) === 1) {
                    $msg2 = tr('The page name was added as header to the following page:');
                } else {
                    $msg2 = tr('The page name was added as header to the following pages:');
                }
                $feedback2 = [
                    'tpl' => 'action',
                    'mes' => $msg2,
                    'items' => $fitems,
                ];
                Feedback::success($feedback2);
            }
            //return to page
            return Services_Utilities::refresh($util->extra['referer']);
        }
    }
}
