<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

//this script may only be included - so it's better to die if called directly.
namespace Tiki\Lib\core\Toolbar;

if (strpos($_SERVER["SCRIPT_NAME"], basename(__FILE__)) !== false) {
    header("location: index.php");
    exit;
}

include_once('lib/smarty_tiki/block.self_link.php');

$toolbarPickerIndex = -1;


class ToolbarsList
{
    private array $lines = [];
    private bool $wysiwyg = false;
    private bool $is_html = false;
    private string $domElementId;
    private string $syntax;

    private function __construct()
    {
    }

    /***
     * @param array  $params       params from smarty_function_toolbars
     * @param array  $tags_to_hide list of tools not to show
     * @param string $domElementId id of the textarea needing the toolbar
     *
     * @return ToolbarsList
     */
    public static function fromPreference(array $params, array $tags_to_hide): ToolbarsList
    {
        global $tikilib;

        $global = $tikilib->get_preference('toolbar_global' . ($params['comments'] === 'y' ? '_comments' : ''));
        $local = $tikilib->get_preference('toolbar_' . $params['section'] . ($params['comments'] === 'y' ? '_comments' : ''), $global);

        foreach ($tags_to_hide as $name) {
            $local = str_replace($name, '', $local);
        }
        if ($params['section'] === 'wysiwyg_plugin') {  // quick fix to prevent nested wysiwyg plugins (messy)
            $local = str_replace('wikiplugin_wysiwyg', '', $local);
        }

        $local = str_replace([',,', '|,', ',|', ',/', '/,'], [',', '|', '|', '/', '/'], $local);

        return self::fromPreferenceString($local, $params);
    }

    public static function fromPreferenceString(string $string, array $params): ToolbarsList
    {
        global $toolbarPickerIndex;
        $toolbarPickerIndex = -1;
        $list = new self();
        $list->wysiwyg = (isset($params['_wysiwyg']) && $params['_wysiwyg'] === 'y');
        $list->is_html = ! empty($params['_is_html']);
        $list->domElementId = $params['area_id'] ?? 'tiki';
        $list->syntax = $params['syntax'] ?? 'tiki';

        $string = preg_replace('/\s+/', '', $string);

        foreach (explode('/', $string) as $line) {
            $bits = explode('|', $line);
            if (count($bits) > 1) {
                $list->addLine(explode(',', $bits[0]), explode(',', $bits[1]));
            } else {
                $list->addLine(explode(',', $bits[0]));
            }
        }

        return $list;
    }

    public function addTag(string $name, bool $unique = false): bool
    {
        if ($unique && $this->contains($name)) {
            return false;
        }
        $this->lines[count($this->lines) - 1][0][0][] = ToolbarItem::getTag($name);
        return true;
    }

    public function insertTag(string $name, bool $unique = false): bool
    {
        if ($unique && $this->contains($name)) {
            return false;
        }
        array_unshift($this->lines[0][0][0], ToolbarItem::getTag($name));
        return true;
    }

    private function addLine(array $tags, array $rtags = []): void
    {
        $elements = [];
        $j = count($rtags) > 0 ? 2 : 1;

        for ($i = 0; $i < $j; $i++) {
            $group = [];
            $elements[$i] = [];

            if ($i == 0) {
                $thetags = $tags;
            } else {
                $thetags = $rtags;
            }
            foreach ($thetags as $tagName) {
                if ($tagName === '-' || $tagName === '|') {
                    if (count($group)) {
                        $elements[$i][] = $group;
                        $group = [];
                    }
                } elseif (
                    ($tag = ToolbarItem::getTag(
                        $tagName,
                        $this->wysiwyg,
                        $this->is_html,
                        $this->syntax === 'markdown',
                        $this->domElementId
                    ))
                    && $tag->isAccessible()
                ) {
                    $group[] = $tag->setDomElementId($this->domElementId);
                }
            }

            if (count($group)) {
                $elements[$i][] = $group;
            }
        }
        if (count($elements)) {
            $this->lines[] = $elements;
        }
    }

    public function getWysiwygArray(): array
    {
        $lines = [];
        $rightAligned = [];
        foreach ($this->lines as $line) {
            $lineOut = [];

            foreach ($line as $index => $bit) {
                foreach ($bit as $group) {
                    $group_count = 0;
                    foreach ($group as $tag) {
                        if ($this->is_html) {
                            if ($token = $tag->getWysiwygToken($this->domElementId)) {
                                $lineOut[] = $token;
                                $group_count++;
                            }
                        } else {
                            if ($this->syntax === 'markdown') {
                                $token = $tag->getMarkdownWysiwyg($this->domElementId);
                                if (in_array($token, $lineOut)) {
                                    // non-wysiwyg has three heading buttons but Toast uses a dropdown, so only add one
                                    // (and array_unique only works on single dimensional arrays)
                                    $token = null;
                                }
                                if ($token) {
                                    if ($index > 0) {
                                        $rightAligned[] = $token;
                                    } else {
                                        $lineOut[] = $token;
                                        $group_count++;
                                    }
                                }
                            } else {
                                $token = $tag->getWysiwygWikiToken($this->domElementId);
                                if ($token) {
                                    $lineOut[] = $token;
                                    $group_count++;
                                }
                            }
                        }
                    }
                    if ($group_count) { // don't add separators for empty groups
                        $lineOut[] = '-';
                    }
                }
            }

            $lineOut = array_slice($lineOut, 0, -1);

            if (count($lineOut)) {
                $lines[] = [$lineOut];
            }
        }

        if ($this->syntax === 'markdown') {
            // need to flatten the icons for toast which only has one toolbar it seems
            $mdLine = [];
            foreach ($lines as $blocks) {
                foreach ($blocks as $block) {
                    foreach ($block as & $item) {
                        if ($decoded = json_decode($item, true)) {
                            $item = $decoded;
                        }
                    }
                    $mdLine[] = array_values(
                        array_filter($block, function ($v) {
                            // separators get added inbetween groups, so remove them
                            return $v !== '-';
                        })
                    );
                }
            }
            foreach ($rightAligned as & $item) {
                if ($decoded = json_decode($item, true)) {
                    $item = $decoded;
                }
            }
            $mdLine[] = $rightAligned;
            $lines = $mdLine;
        }

        return $lines;
    }

    public function getWikiHtml(): string
    {
        $html = '';

        $c = 0;
        foreach ($this->lines as $line) {
            $lineHtml = '';
            $right = '';
            if (count($line) == 1) {
                $line[1] = [];
            }

            // $line[0] is left part, $line[1] right floated section
            for ($bitx = 0, $bitxcount_line = count($line); $bitx < $bitxcount_line; $bitx++) {
                $lineBit = '';

                foreach ($line[$bitx] as $group) {
                    $groupHtml = '';
                    foreach ($group as $tag) {
                        if ($this->syntax === 'markdown') {
                            $groupHtml .= $tag->getMarkdownHtml($this->domElementId);
                        } else {
                            $groupHtml .= $tag->getWikiHtml($this->domElementId);
                        }
                    }

                    if (! empty($groupHtml)) {
                        $param = ' class="toolbar-list"';
                        $lineBit .= "<span$param>$groupHtml</span>";
                    }
                    if ($bitx == 1) {
                        if (! empty($right)) {
                            $right = '<span class="toolbar-list">' . $right . '</span>';
                        }
                        $lineHtml = "<div class='helptool-admin float-end'>$lineBit $right</div>" . $lineHtml;
                    } else {
                        $lineHtml = $lineBit;
                    }
                }

                // adding admin icon if no right part - messy - TODO better
                if ($c == 0 && empty($lineBit) && ! empty($right)) {
                    $lineHtml .= "<div class='helptool-admin float-end'>$right</div>";
                }
            }
            if (! empty($lineHtml)) {
                $html .= "<div>$lineHtml</div>";
            }
            $c++;
        }

        return $html;
    }

    public function contains(string $name): bool
    {

        foreach ($this->lines as $line) {
            foreach ($line as $group) {
                foreach ($group as $tags) {
                    foreach ($tags as $tag) {
                        if ($tag->getLabel() == $name) {
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }
}
