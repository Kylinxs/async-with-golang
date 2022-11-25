
<?php

namespace Tiki\Lib\core\Toolbar;

use TikiLib;

class ToolbarHelptool extends ToolbarUtilityItem
{
    private string $onClick = '';

    public function __construct()
    {
        $this->setLabel(tra('Wiki Help'))
            ->setIcon('img/icons/help.png')
            ->setIconName('help')
            ->setType('Helptool')
            ->setWysiwygToken('tikihelp')
            ->setMarkdownSyntax('tikihelp')
            ->setMarkdownWysiwyg('tikihelp')
            ->setClass('qt-help');
    }

    public function getWikiHtml(): string
    {
        return $this->getPlainHtml();
    }

    public function getMarkdownHtml(): string
    {
        return self::getPlainHtml(true);
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function getPlainHtml(bool $isMarkdown = false): string
    {
        $smarty = TikiLib::lib('smarty');
        $servicelib = TikiLib::lib('service');

        $params = ['controller' => 'edit', 'action' => 'help', 'modal' => 1];
        if ($isMarkdown) {
            $params['markdown'] = 1;
        } else {
            $params['wiki'] = 1;
        }
        $params['plugins'] = 1;
        $params['areaId'] = $this->domElementId;

        if ($GLOBALS['section'] == 'sheet') {
            $params['sheet'] = 1;
        }

        $smarty->loadPlugin('smarty_function_icon');
        $icon = smarty_function_icon(['name' => 'help'], $smarty->getEmptyInternalTemplate());
        $url = $servicelib->getUrl($params);
        $help = tra('Help');

        return "<a title=\":$help\" class=\"toolbar btn btn-sm px-2 qt-help tips bottom\" href=\"$url\" data-bs-toggle=\"modal\" data-bs-target=\"#bootstrap-modal\">$icon</a>";
    }

    public function getWysiwygToken(): string
    {

        $this->setupCKEditorTool($this->getWysiwygJs());

        return 'tikihelp';
    }

    public function getMarkdownWysiwyg(): string
    {

        $this->onClick = $this->getWysiwygJs(true);

        return parent::getMarkdownWysiwyg();
    }

    private function getWysiwygJs(bool $isMarkdown = false): string
    {
        global $section;

        $servicelib = TikiLib::lib('service');

        $params = ['controller' => 'edit', 'action' => 'help', 'modal' => 1];
        if ($isMarkdown) {
            $params['markdown_wysiwyg'] = 1;
        } else {
            $params['wysiwyg'] = 1;
        }
        $params['plugins'] = 1;

        if ($section == 'sheet') {
            $params['sheet'] = 1;
        }

        // multiple ckeditors share the same toolbar commands, so area_id (editor.name) must be added when clicked
        $params['areaId'] = $this->domElementId;

        $this->setLabel(tra('WYSIWYG Help'));

        return '$.openModal({show: true, remote: "' . $servicelib->getUrl($params) . '"});';
    }
    protected function getOnClick(): string
    {
        // set by markdown wysiwyg
        return $this->onClick;
    }
}