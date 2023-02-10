
<?php

namespace Tiki\Lib\core\Toolbar;

class ToolbarSheet extends ToolbarItem
{
    protected string $syntax;

    public static function fromName(string $tagName): ?ToolbarItem
    {
        switch ($tagName) {
            case 'sheetsave':
                $label = tra('Save Sheet');
                $iconname = 'floppy';
                $syntax = '
                    $("#saveState").hide();
                    $.sheet.saveSheet($.sheet.tikiSheet, function() {
                        $.sheet.manageState($.sheet.tikiSheet, true);
                    });';
                break;
            case 'addrow':
                $label = tra('Add row after selection or to the end if no selection');
                $icon = tra('img/icons/sheet_row_add.png');
                $syntax = 'sheetInstance.controlFactory.addRow();'; // add row after end to workaround bug in jquery.sheet.js 1.0.2
                break;                                                      // TODO fix properly for 5.1
            case 'addrowmulti':
                $label = tra('Add multiple rows after selection or to the end if no selection');
                $icon = tra('img/icons/sheet_row_add_multi.png');
                $syntax = 'sheetInstance.controlFactory.addRowMulti();';
                break;
            case 'addrowbefore':
                $label = tra('Add row before selection or to end if no selection');
                $icon = tra('img/icons/sheet_row_add.png');
                $syntax = 'sheetInstance.controlFactory.addRow(null, true);';   // add row after end to workaround bug in jquery.sheet.js 1.0.2
                break;
            case 'deleterow':
                $label = tra('Delete selected row');
                $icon = tra('img/icons/sheet_row_delete.png');
                $syntax = 'sheetInstance.deleteRow();';
                break;
            case 'addcolumn':
                $label = tra('Add column after selection or to the end if no selection');
                $icon = tra('img/icons/sheet_col_add.png');
                $syntax = 'sheetInstance.controlFactory.addColumn();';  // add col before current or at end if none selected
                break;
            case 'deletecolumn':
                $label = tra('Delete selected column');
                $icon = tra('img/icons/sheet_col_delete.png');
                $syntax = 'sheetInstance.deleteColumn();';
                break;
            case 'addcolumnmulti':
                $label = tra('Add multiple columns after selection or to the end if no selection');
                $icon = tra('img/icons/sheet_col_add_multi.png');
                $syntax = 'sheetInstance.controlFactory.addColumnMulti();';
                break;
            case 'addcolumnbefore':
                $label = tra('Add column before selection or to the end if no selection');
                $icon = tra('img/icons/sheet_col_add.png');
                $syntax = 'sheetInstance.controlFactory.addColumn(null, true);';    // add col before current or at end if none selected
                break;
            case 'sheetgetrange':
                $label = tra('Get Cell Range');
                $icon = tra('img/icons/sheet_get_range.png');
                $syntax = 'sheetInstance.getTdRange(null, sheetInstance.obj.formula().val()); return false;';
                break;
            case 'sheetfind':
                $label = tra('Find');
                $iconname = 'search';
                $syntax = 'sheetInstance.cellFind();';
                break;
            case 'sheetrefresh':
                $label = tra('Refresh calculations');
                $iconname = 'refresh';
                $syntax = 'sheetInstance.calc();';
                break;
            case 'sheetclose':
                $label = tra('Finish editing');
                $iconname = 'delete';
                $syntax = '$.sheet.manageState(sheetInstance.obj.parent(), true);'; // temporary workaround TODO properly
                break;
            case 'bold':
                $label = tra('Bold');
                $iconname = 'bold';
                $syntax = 'sheetInstance.cellStyleToggle("styleBold");';
                break;
            case 'italic':
                $label = tra('Italic');
                $iconname = 'italic';
                $syntax = 'sheetInstance.cellStyleToggle("styleItalics");';
                break;
            case 'underline':
                $label = tra('Underline');
                $iconname = 'underline';
                $syntax = 'sheetInstance.cellStyleToggle("styleUnderline");';
                break;
            case 'strike':
                $label = tra('Strikethrough');
                $iconname = 'strikethrough';
                $syntax = 'sheetInstance.cellStyleToggle("styleLineThrough");';
                break;
            case 'center':
                $label = tra('Align Center');
                $iconname = 'align-center';
                $syntax = 'sheetInstance.cellStyleToggle("styleCenter");';
                break;
            default:
                return null;
        }

        $tag = new self();
        $tag->setLabel($label)
            ->setSyntax($syntax)
            ->setType('Sheet')
            ->setClass('qt-sheet');

        if (! empty($iconname)) {
            $tag->setIconName(! empty($iconname) ? $iconname : 'help');
        }
        if (! empty($icon)) {
            $tag->setIcon(! empty($icon) ? $icon : 'img/icons/shading.png');
        }

        return $tag;
    }

    /**
     * @return string
     */
    public function getOnClick(): string
    {
        return addslashes(htmlentities($this->syntax, ENT_COMPAT, 'UTF-8'));
    }
}