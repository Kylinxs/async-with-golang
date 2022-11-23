<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

use Tiki\File\DiagramHelper;
use Tiki\Package\VendorHelper;
use Tiki\FileGallery\File as TikiFile;

function wikiplugin_diagram_info()
{
    $info = [
        'name' => tr('Diagram'),
        'documentation' => 'PluginDiagram',
        'description' => tr('Display diagrams.net/mxGraph/Draw.io diagrams'),
        'prefs' => ['wikiplugin_diagram'],
        'iconname' => 'sitemap',
        'tags' => ['basic'],
        'introduced' => 19,
        'packages_required' => [
            'tikiwiki/diagram' => VendorHelper::getAvailableVendorPath('diagram', 'tikiwiki/diagram/js/app.min.js')
        ],
        'params' => [
            'fileId' => [
                'required' => false,
                'name' => tr('fileId'),
                'description' => tr('Id of the file in the file gallery. A xml file containing the graph model. Leave empty for more options.'),
                'since' => '19.0',
                'filter' => 'int',
            ],
            'page' => [
                'required' => false,
                'name' => tr('page'),
                'description' => tr('Page of the diagram that should be displayed.'),
                'since' => '',
                'filter' => 'text',
            ],
            'annotate' => [
                'required' => false,
                'name' => tr('annotate'),
                'description' => tr('Id of the file in the file gallery. A image file to include in the diagram.'),
                'since' => '20.0',
                'filter' => 'int',
            ],
            'align' => [
                'required' => false,
                'name' => tr('Image alignment when exporting a PDF'),
                'description' => tr('Alignment of the diagrams during PDF export. Accepted values: "left", "right" and "center"'),
                'default' => 'left',
                'since' => '21.0',
            ],
            'wikiparse' => [
                'required' => false,
                'name' => tr('Parse wiki markup language inside the diagram'),
                'description' => tr('Parameter that will allow to parse wiki markup language inside the diagram if the value is "1"'),
                'since' => '21.0',
            ],
            'compressXml' => [
                'required' => false,
                'name' => tr('Compress XML'),
                'description' => tr('Parameter that will allow inline diagram data be saved compressed.'),
                'since' => '21.0',
            ],
            'template' => [
                'required' => false,
                'name' => tr('template'),
                'description' => tr('Diagram\'s file id to use as a template to new the diagram. This parameter will be skipped if the fileId parameter is present.'),
                'since' => '23.0',
                'filter' => 'int',
            ],
            'galleryId' => [
                'required' => false,
                'name' => tr('Gallery Id'),
                'description' => tr('File Gallery id where the new diagram file will be stored. If this parameter is not present the content of 
                the file will be placed in the body of the plugin.'),
                'since' => '23.0',
                'filter' => 'int',
            ],
            'fileName' => [
                'required' => false,
                'name' => tr('File Name'),
                'description' => tr('The name used for the diagram file. Acceptable replacements are \'%page%\' and \'%date%\'. The default pattern is “Diagram %page% %date%.drawio”.'),
                'since' => '23.0',
                'filter' => 'text',
            ]
        ],
    ];

    return $info;
}

/**
 * @param $data
 * @param $params
 * @return string
 * @throws Exception
 */
function wikiplugin_diagram($data, $params)
{
    global $tikilib, $user, $page, $wikiplugin_included_page, $prefs, $tiki_p_upload_files;

    $template = $params['template'];
    $galleryId = $params['galleryId'] ?? ($params['fileName'] ? 1 : '');
    $fileName = $params['fileName'] ?? 'Diagram %page% %date%.drawio' ;
    $fileName = preg_replace('/\%page\%/', $page, $fileName);
    $fileName = preg_replace('/\%date\%/', date('Y-m-d'), $fileName);

    $compressXml = ($prefs['fgal_use_diagram_compression_by_default'] !== 'y') ? false : true;
    $compressXmlParam = false;

    if (empty($params['fileId']) && empty(trim($data)) && ! empty($params['template'])) {
        $params['fileId'] = $template;
    }

    if (
        empty($params['fileId'])
        && isset($params['compressXml'])
        && in_array($params['compressXml'], ['false', '0'])
    ) {
            $compressXml = false;
            $compressXmlParam = true;
    }

    $diagramIdentifier = ! empty($params['fileId']) ? $params['fileId'] : $data;
    $info = wikiplugin_diagram_info();
    $pageName = isset($params['page']) ? $params['page'] : '';
    $diagrams = DiagramHelper::getDiagramsFromIdentifier($diagramIdentifier, $pageName);

    if (! empty($params['align']) && in_array($params['align'], ['left', 'center', 'right'])) {
        $alignment = $params['align'];
    } else {
        $alignment = $info['params']['align']['default'];
    }

    if (! empty($_GET['display']) && $_GET['display'] == 'pdf') {
        $html = '';

        foreach ($diagrams as $diagram) {
            $html .= ! empty($html) ? '<br/>' : '';
            $html .= '<div style="text-align:' . $alignment . ';">' .
                '<img src="data:image/png;base64,' . DiagramHelper::getDiagramAsImage($diagram) . '"></div>';
        }

        return $html;
    }

    $filegallib = TikiLib::lib('filegal');

    $errorMessageToAppend = '';
    $oldVendorPath = VendorHelper::getAvailableVendorPath('mxgraph', 'xorti/mxgraph-editor/drawio/webapp/js/app.min.js', false);
    if ($oldVendorPath) {
        $errorMessageToAppend = tr('Previous xorti/mxgraph-editor package has been deprecated.<br/>');
    }

    $vendorPath = VendorHelper::getAvailableVendorPath('diagram', 'tikiwiki/diagram/js/app.min.js', false);
    if (! $vendorPath) {
        $message = $errorMessageToAppend;
        $message .= tr('To view diagrams Tiki needs the tikiwiki/diagram package. If you do not have permission to install this package, ask the site administrator.');
        Feedback::error($message);
        return;
    }

    $headerlib = TikiLib::lib('header');
    $headerlib->add_js_config("var diagramVendorPath = '{$vendorPath}';");
    $headerlib->add_jsfile('lib/jquery_tiki/tiki-mxgraph.js', true);
    $headerlib->add_jsfile($vendorPath . '/tikiwiki/diagram/js/app.min.js');

    $headerlib->add_css('.diagram hr {margin-top:0.5em;margin-bottom:0.5em}');

    $fileId = isset($params['fileId']) ? intval($params['fileId']) : 0;
    $annotate = isset($params['annotate']) ? intval($params['annotate']) : 0;

    if ($fileId) {
        $file = \Tiki\FileGallery\File::id($fileId);
        $data = $file->getContents();

        if ($data === false) {
            Feedback::error(tr("Tiki wasn't able to find the file with id %0.", $fileId));
            return;
        }
    }

    $data = DiagramHelper::parseData($data);
    static $diagramIndex = 0;
    ++$diagramIndex;

    if (function_exists('simplexml_load_string')) {
        $doc = simplexml_load_string($data);
        if ($doc !== false && ($doc->getName() != 'mxGraphModel' && $doc->getName() != 'mxfile')) {
            Feedback::error(tr("Tiki wasn't able to parse the Diagram. Please check the diagram XML data and structure."));
            return;
        } elseif (empty($data) || $doc === false) {
            if ($tiki_p_upload_files != 'y') {
                return;
            }

            $label = tra('Create New Diagram');
            $page = htmlentities($page);
            $in = tr(" in ");

            $gals = $filegallib->list_file_galleries(0, -1, 'name_desc', $user);
            $galHtml = "";
            if (! isset($params['fileName']) && ! isset($params['galleryId'])) {
                $galHtml = "<option value='0'>" . tr('Page (inline)') . "</option>";
            }
            usort($gals['data'], function ($a, $b) {
                return strcmp(strtolower($a['name']), strtolower($b['name']));
            });

            if (! isset($params['galleryId'])) {
                foreach ($gals['data'] as $gal) {
                    if ($gal['name'] != "Wiki Attachments" && $gal['name'] != "Users File Galleries") {
                        if ($gal['parentId'] == -1) {
                            // If the current user has permission to access the gallery, then add gallery to the hierarchy.
                            if ($gal['perms']['tiki_p_view_file_gallery'] == 'y') {
                                $galHtml .= "<option value='" . $gal['id'] . "'>" . htmlentities($gal['name']);
                            }
                            $galHtml .= $filegallib->getNodes($gals['data'], $gal['id'], "");
                            $galHtml .= "</option>";
                        }
                    }
                }
            }

            if ($annotate && $infoImg = loadImageAnnotate($annotate)) {
                $data = <<<XML
<mxGraphModel grid="1" gridSize="10" guides="1" tooltips="1" connect="1" arrows="1" fold="1" page="1" pageScale="1" pageWidth="850" pageHeight="1100" background="#ffffff">
  <root>
    <mxCell id="0"/>
    <mxCell id="1" parent="0"/>
    <mxCell id="2" value="" style="shape=image;imageAspect=0;aspect=fixed;verticalLabelPosition=bottom;verticalAlign=top;image={$infoImg['url']};imageBackground=none;movable=0;resizable=0;rotatable=0;deletable=0;editable=0;connectable=0;" parent="1" vertex="1">
      <mxGeometry width="{$infoImg['imageSize'][0]}" height="{$infoImg['imageSize'][1]}" as="geometry"/>
    </mxCell>
  </root>
</mxGraphModel>
XML;
                $data = DiagramHelper::parseData($data);
                $data = base64_encode($data);
            }

            if (! empty($galHtml)) {
                $galHtml = <<<EOF
                    $in                
                    <select name="galleryId" class="form-control-sm">
                        $galHtml
                    </select>
                EOF;
            } else {
                $galHtml = <<<EOF
                    <input type="hidden" name="galleryId" value="$galleryId"/>
                EOF;
            }

            return <<<EOF
        ~np~
        <form id="newDiagram$diagramIndex" method="post" action="tiki-editdiagram.php">
            <p>
                <input type="submit" class="btn btn-primary btn-sm" name="label" value="$label" class="newSvgButton" />
                $galHtml
                <input type="hidden" name="newDiagram" value="1"/>
                <input type="hidden" name="page" value="$page"/>
                <input type="hidden" name="template" value="$template"/>
                <input type="hidden" name="fileName" value="$fileName"/>
                <input type="hidden" name="xml" value='$data'/>
                <input type="hidden" name="index" value="$diagramIndex"/>
            </p>
        </form>
        ~/np~
EOF;
        }
    }

    //checking if user can see edit button
    if (! empty($wikiplugin_included_page)) {
        $sourcepage = $wikiplugin_included_page;
    } else {
        $sourcepage = $page;
    }

    //checking if user has edit permissions on the wiki page/file using the current permission library to obey global/categ/object perms
    if ($fileId) {
        $objectperms = Perms::get(['type' => 'file', 'object' => $fileId]);
    } else {
        $objectperms = Perms::get([ 'type' => 'wiki page', 'object' => $sourcepage ]);
    }

    if ($objectperms->edit) {
        $allowEdit = true;
    } else {
        $allowEdit = false;
    }

    $base_64_diagram = base64_encode($data);

    if (isset($params['wikiparse']) && $params['wikiparse'] == 1) {
        $parsedDiagrams = [];
        $XMLDiagrams = simplexml_load_string($data);

        if ($XMLDiagrams->getName() == 'mxGraphModel') {
            $parsedDiagrams = ['<diagram id="' . uniqid() . '">' . DiagramHelper::parseDiagramWikiSyntax($XMLDiagrams) . '</diagram>'];
        } else {
            if (! empty($XMLDiagrams)) {
                foreach ($XMLDiagrams as $diagram) {
                    $parsedDiagram = DiagramHelper::parseDiagramWikiSyntax((string) $diagram);
                    $diagram[0] = $parsedDiagram;
                    $test = $diagram->saveXML();
                    $parsedDiagrams[] = $diagram->saveXML();
                }
            }
        }

        $diagrams = $parsedDiagrams;
        $data = '<mxfile>' . implode('', $diagrams) . '</mxfile>';
    }

    $slidePage = explode("/", $_SERVER['PHP_SELF']);
    $slidePage = end($slidePage);

    $smarty = TikiLib::lib('smarty');
    $smarty->assign('index', $diagramIndex);
    $smarty->assign('data', $diagrams);
    $smarty->assign('graph_data_base64', $base_64_diagram);
    $smarty->assign('sourcepage', $sourcepage);
    $smarty->assign('allow_edit', $allowEdit);
    $smarty->assign('file_id', $fileId);
    $smarty->assign('template', $template);
    $smarty->assign('gallery_id', $galleryId);
    $smarty->assign('file_name', ($fileId == $template) ? $fileName : $file->name);
    $smarty->assign('alignment', $alignment);
    $smarty->assign('mxgraph_prefix', $vendorPath);
    $smarty->assign('page_name', $pageName);
    $smarty->assign('compressXml', $compressXml);
    $smarty->assign('compressXmlParam', $compressXmlParam);
    $smarty->assign('slide_psge', $slidePage);

    return '~np~' . $smarty->fetch('wiki-plugins/wikiplugin_diagram.tpl') . '~/np~';
}

/**
 * Get info of the image to annotate on
 *
 * @param number $annotate
 * @return array | false
 * @throws SmartyException
 */
function loadImageAnnotate($annotate)
{
    global $user;

    $userLib = TikiLib::lib('user');
    $file = \Tiki\FileGallery\File::id($annotate);

    $smarty = TikiLib::lib('smarty');
    $smarty->loadPlugin('smarty_modifier_sefurl');
    $url = smarty_modifier_sefurl($annotate, 'display');

    if (! $file->exists() || ! $userLib->user_has_perm_on_object($user, $file->fileId, 'file', 'tiki_p_download_files')) {
        Feedback::error(tr("Tiki wasn't able to find the file with id %0.", $annotate));
        return false;
    }

    if (! preg_match('/^image\//', $file->filetype)) {
        Feedback::error(tr("Selected file to annotate must be an image."));
        return false;
    }

    if ($file->getWrapper()->isFileLocal()) {
        $imageSize = getimagesize($file->getWrapper()->getReadableFile());
    } else {
        $data = $file->getContents();
        $imageSize = getimagesize('data://text/plain;base64,' . base64_encode($data));
    }

    if (empty($imageSize)) {
        Feedback::error(tr("Can not retrieve size from file %0", $annotate));
        return false;
    }

    return [
        'url' => $url,
        'imageSize' => $imageSize
    ];
}
