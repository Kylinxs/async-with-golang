
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

class WikiParser_PluginOutput
{
    private $format;
    private $data;

    private function __construct($format, $data)
    {
        $this->format = $format;
        $this->data = $data;
    }

    public static function wiki($text)
    {
        return new self('wiki', $text);
    }

    public static function html($html)
    {
        return new self('html', $html);
    }

    public static function internalError($message)
    {
        return self::error(tra('Internal error'), $message);
    }

    public static function userError($message)
    {
        return self::error(tra('User error'), $message);
    }

    public static function argumentError($missingArguments)
    {
        $content = tra('Plugin argument(s) missing:');

        $content .= '<ul>';

        foreach ($missingArguments as $arg) {
            $content .= "<li>$arg</li>";
        }

        $content .= '</ul>';

        return self::userError($content);
    }

    public static function error($label, $message)
    {
        $smarty = TikiLib::lib('smarty');
        $smarty->loadPlugin('smarty_block_remarksbox');
        $repeat = false;

        return new self(
            'html',
            smarty_block_remarksbox(
                [
                            'type' => 'error',
                            'title' => $label,
                        ],
                $message,
                $smarty,
                $repeat
            )
        );
    }

    public static function disabled($name, $preferences)
    {
        // this will allow us to return to where we were if the required preference is activated in the page
        $accessLib = TikiLib::lib('access');
        $gobackto = $accessLib->getOriginUrl();

        $content = tr('Plugin <strong>%0</strong> cannot be executed.', $name);

        if (Perms::get()->admin) {
            $smarty = TikiLib::lib('smarty');
            $smarty->loadPlugin('smarty_function_preference');
            $smarty->loadPlugin('smarty_modifier_escape');
            $smarty->loadPlugin('smarty_function_ticket');
            $content .= '<form method="post" action="tiki-admin.php">';
            foreach ($preferences as $pref) {
                $content .= smarty_function_preference(['name' => $pref], $smarty->getEmptyInternalTemplate());
            }
            $content .= smarty_function_ticket([], $smarty->getEmptyInternalTemplate());
            $content .= '<input type="submit" class="btn btn-primary btn-sm" value="'
                . smarty_modifier_escape(tra('Set')) . '">';
            if (! empty($gobackto)) {
                $content .= '<input type="hidden" name="gobackto" value="' . $gobackto . '" >';
            }
            $content .= '</form>';
        }
        return self::error(tra('Plugin disabled'), $content);
    }

    public function toWiki()
    {
        switch ($this->format) {
            case 'wiki':
                return $this->data;
            case 'html':
                return "~np~{$this->data}~/np~";
        }
    }

    public function toHtml($parseOptions = [])
    {
        switch ($this->format) {
            case 'wiki':
                return $this->parse($this->data, $parseOptions);
            case 'html':
                return $this->data;
        }
    }

    private function parse($data, $parseOptions = [])
    {
        global $tikilib;

        return TikiLib::lib('parser')->parse_data($data, $parseOptions);
    }
}