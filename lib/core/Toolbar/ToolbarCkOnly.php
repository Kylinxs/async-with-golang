
<?php

namespace Tiki\Lib\core\Toolbar;

use TikiLib;

class ToolbarCkOnly extends ToolbarItem
{
    public function __construct($token, $icon = '', $iconname = '')
    {
        if (empty($icon)) {
            $img_path = 'img/ckeditor/' . strtolower($token) . '.png';
            if (is_file($img_path)) {
                $icon = $img_path;
            } else {
                $icon = 'img/icons/shading.png';
            }
        }
        $this->setWysiwygToken($token)
            ->setIcon($icon)
            ->setIconName($iconname)
            ->setType('CkOnly');
    }

    public static function fromName(string $name, bool $is_html, bool $is_markdown): ?ToolbarItem
    {
        global $prefs;

        if ($is_markdown) {
            return null;
        }

        switch ($name) {
            case 'templates':
                if ($prefs['feature_wiki_templates'] === 'y') {
                    return new self('Templates');
                } else {
                    return null;
                }
            case 'cut':
                return new self('Cut', null, 'scissors');
            case 'copy':
                return new self('Copy', null, 'copy');
            case 'paste':
                return new self('Paste', null, 'paste');
            case 'pastetext':
                return new self('PasteText', null, 'paste');
            case 'pasteword':
                return new self('PasteFromWord', null, 'paste');
            case 'print':
                return new self('Print', null, 'print');
            case 'spellcheck':
                return new self('SpellChecker', null, 'ok');
            case 'undo':
                return new self('Undo', null, 'undo');
            case 'redo':
                return new self('Redo', null, 'repeat');
            case 'selectall':
                return new self('SelectAll', null, 'selectall');
            case 'removeformat':
                return new self('RemoveFormat', null, 'erase');
            case 'showblocks':
                return new self('ShowBlocks', null, 'box');
            case 'left':
                return new self('JustifyLeft', null, 'align-left');
            case 'right':
                return new self('JustifyRight', null, 'align-right');
            case 'full':
                return new self('JustifyBlock', null, 'align-justify');
            case 'indent':
                return new self('Indent', null, 'indent');
            case 'outdent':
                return new self('Outdent', null, 'outdent');
            case 'style':
                return new self('Styles');
            case 'fontname':
                return new self('Font');
            case 'fontsize':
                return new self('FontSize');
            case 'format':
                return new self('Format');
            case 'source':
                global $tikilib, $user, $page;
                $p = $prefs['wysiwyg_htmltowiki'] == 'y' ? 'tiki_p_wiki_view_source' : 'tiki_p_use_HTML';
                if ($tikilib->user_has_perm_on_object($user, $page, 'wiki page', $p)) {
                    return new self('Source', null, 'code_file');
                } else {
                    return null;
                }
            case 'autosave':
                return new self('autosave', 'img/ckeditor/ajaxSaveDirty.gif', 'floppy');
            case 'inlinesave':
                return new self('Inline save', 'img/ckeditor/ajaxSaveDirty.gif');
            case 'inlinecancel':
                return new self('Inline cancel', 'img/icons/cross.png');
            case 'sub':
                return new self('Subscript', null, 'subscript');
            case 'sup':
                return new self('Superscript', null, 'subscript');
            case 'anchor':
                return new self('Anchor', null, 'anchor');
            case 'bidiltr':
                return new self('BidiLtr', null, 'arrow-right');
            case 'bidirtl':
                return new self('BidiRtl', null, 'arrow-left');
            case 'image':
                return new self('Image', null, 'image');
            case 'table':
                return $is_html ? new self('Table') : null;
            case 'link':
                return $is_html ? new self('Link') : null;
            case 'unlink':
                return new self('Unlink', null, 'unlink');
        }
        return null;
    }

    public function getWikiHtml(): string
    {
        return '';
    }

    public function getWysiwygToken(): string
    {
        if ($this->wysiwyg === 'Image') {   // cke's own image tool
            global $prefs;
            $headerlib = TikiLib::lib('header');
            // can't do upload the cke way yet
            $url = 'tiki-list_file_gallery.php?galleryId=' . $prefs['home_file_gallery'] . '&filegals_manager=fgal_picker';
            $headerlib->add_js(
                'if (typeof window.CKEDITOR !== "undefined") {window.CKEDITOR.config.filebrowserBrowseUrl = "' . $url . '"}',
                5
            );
        }
        return $this->wysiwyg;
    }

    public function getWysiwygWikiToken(): string // wysiwyg_htmltowiki
    {
        switch ($this->wysiwyg) {
            case 'autosave':
            case 'Copy':
            case 'Cut':
            case 'Format':
            case 'JustifyLeft':
            case 'Paste':
            case 'PasteText':
            case 'PasteFromWord':
            case 'Redo':
            case 'RemoveFormat':
            case 'ShowBlocks':
            case 'Source':
            case 'Undo':
            case 'Unlink':
                return $this->wysiwyg;
            default:
                return '';
        }
    }

    public function getLabel(): string
    {
        return $this->wysiwyg;
    }

    public function getIconHtml(): string
    {
        if (! empty($this->iconname)) {
            $smarty = TikiLib::lib('smarty');
            $smarty->loadPlugin('smarty_function_icon');
            return smarty_function_icon(
                [
                                            'name'   => $this->iconname,
                                            'ititle' => ':'
                                                . htmlentities(
                                                    $this->getLabel(),
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ),
                                            'iclass' => 'tips bottom',
                                        ],
                $smarty->getEmptyInternalTemplate()
            );
        }
        if ((! empty($this->icon) && $this->icon !== 'img/icons/shading.png') || in_array($this->label, ['Autosave'])) {
            return parent::getIconHtml();
        }

        global $prefs;
        $skin = $prefs['wysiwyg_toolbar_skin'];
        $headerlib = TikiLib::lib('header');
        $headerlib->add_cssfile('vendor_bundled/vendor/ckeditor/ckeditor/skins/' . $skin . '/editor.css');
        $cls = strtolower($this->wysiwyg);
        $headerlib->add_css(
            'span.cke_skin_' . $skin . ' {border: none;background: none;padding:0;margin:0;}' .
            '.toolbars-admin .row li.toolbar > span.cke_skin_' . $skin . ' {display: inline-block;}'
        );
        return '<span class="cke_skin_' . $skin
            . '"><a class="cke_button cke_ltr" style="margin-top:-5px"><span class="cke_button__'
            . htmlentities($cls, ENT_QUOTES, 'UTF-8') . '_icon"' .
            ' title="' . htmlentities($this->getLabel(), ENT_QUOTES, 'UTF-8') . '">' .
            '<span class="cke_icon"> </span>' .
            '</span></a></span>';
    }

    protected function getOnClick(): string
    {
        return '';
    }
}