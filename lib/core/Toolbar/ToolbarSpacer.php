<?php

namespace Tiki\Lib\core\Toolbar;

class ToolbarSpacer extends ToolbarItem
{
    public function __construct()
    {
        $this->setWysiwygToken('|')
            ->setIcon('img/trans.png')
            ->setType('Spacer');
    }

    public function getWikiHtml(): string
    {
        return '||';
    }

    protected function getOnClick(): string
    {
        return '';
    }
}
