<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

function prefs_print_list()
{
    $modules = [
        'top_modules' => 'Top modules',
        'topbar_modules' => 'Topbar modules',
        'pagetop_modules' => 'Pagetop modules',
        'left_modules' => 'Left modules',
        'right_modules' => 'Right modules',
        'pagebottom_modules' => 'Pagebottom modules',
        'bottom_modules' => 'Bottom modules',
        'admin_modules' => 'Admin modules',
    ];

    return [
        'print_pdf_from_url' => [
            'name' => tra('PDF from URL'),
            'description' => tra('Using external tools, generate PDF documents from URLs.'),
            'type' => 'list',
            'options' => [
                'none' => tra('Disabled'),
                'webkit' => tra('WebKit (wkhtmltopdf)'),
                'weasyprint' => tra('WeasyPrint'),
                'webservice' => tra('Webservice'),
                'mpdf' => tra('mPDF'),
            ],
            'default' => 'none',
            'help' => 'PDF',
        ],
        'print_pdf_webservice_url' => [
            'name' => tra('Webservice URL'),
            'description' => tra('URL to a service that takes a URL as the query string and returns a PDF document'),
            'type' => 'text',
            'size' => 50,
            'dependencies' => ['auth_token_access'],
            'default' => '',
        ],
        'print_pdf_webkit_path' => [
            'name' => tra('WebKit path'),
            'description' => tra('Full path to the wkhtmltopdf executable to generate the PDF document with'),
            'type' => 'text',
            'size' => 50,
            'help' => 'wkhtmltopdf',
            'dependencies' => ['auth_token_access'],
            'default' => '',
        ],
        'print_pdf_weasyprint_path' => [
            'name' => tra('WeasyPrint path'),
            'description' => tra('Full path to the weasyprint executable to generate the PDF document with'),
            'type' => 'text',
            'size' => 50,
            'help' => 'weasyprint',
            'dependencies' => ['auth_token_access'],
            'default' => '',
        ],
        'print_pdf_mpdf_printfriendly' => [
            'name' => tra('Print Friendly PDF'),
            'description' => tra('Useful for dark themes, enabling this option will change the theme background color to white and the color of text to black. If not activated, theme colors will be retained in the pdf file.'),
            'type' => 'flag',
            'default' => 'y'

        ],
        'print_pdf_mpdf_orientation' => [
            'name' => tra('PDF Orientation'),
            'description' => tra('Landscape or portrait'),
            'tags' => ['advanced'],
            'type' => 'list',
            'options' => [
                'P' => tra('Portrait'),
                'L' => tra('Landscape'),
            ],
            'default' => 'P',
            'packages_required' => ['mpdf/mpdf' => 'Mpdf\\Mpdf'],
        ],
        'print_pdf_mpdf_size' => [
            'name' => tra('PDF page size'),
            'description' => tra('ISO Standard sizes: A0, A1, A2, A3, A4, A5 or North American paper sizes: Letter, Legal, Tabloid/Ledger (for ledger, select landscape orientation)'),
            'tags' => ['advanced'],
            'type' => 'list',
            'options' => [
                'Letter' => tra('Letter'),
                'Legal' => tra('Legal'),
                'Tabloid' => tra('Tabloid/Ledger'),
                'A0' => tra('A0'),
                'A1' => tra('A1'),
                'A2' => tra('A2')