<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/* This library is LGPL
 * written by Louis-Philippe Huberdeau
 *
 * vim: fdm=marker tabstop=4 shiftwidth=4 noet:
 *
 * This file contains the PDF graphic renderer. (Using PDFLib)
 */
require_once('lib/graph-engine/core.php');

class PDFLib_GRenderer extends GRenderer // {{{1
{
    public $pdf;
    public $styles;
    public $font;

    public $width;
    public $height;

    public function __construct($format = null, $orientation = 'landscape') // {{{2
    {
        // Null size does not create a graphic.
        $this->styles = [];
        $this->font = null;

        if (! is_null($format)) {
            $size = $this->_getFormat($format, $orientation);
            $this->width = $size[0];
            $this->height = $size[1];

            $this->pdf = pdf_new();
            pdf_open_file($this->pdf, '');
            pdf_begin_page($this->pdf, $this->width, $this->height);

            $this->font = pdf_findfont($this->pdf, 'Helvetica', 'builtin', 0);
        }
    }

    public function addLink($target, $left, $top, $right, $bottom, $title = null) // {{{2
    {
    }

    public function drawLine($x1, $y1, $x2, $y2, $style) // {{{2
    {
        $this->_convertPosition($x1, $y1);
        $this->_convertPosition($x2, $y2);

        pdf_setcolor(
            $this->pdf,
            'stroke',
            $style['line'][0],
            $style['line'][1],
            $style['line'][2],
            $style['line'][3],
            $style['line'][4]
        );

        pdf_setlinewidth($this->pdf, $style['line-width']);

        pdf_moveto($this->pdf, $x1, $y1);
        pdf_lineto($this->pdf, $x2, $y2);
        pdf_stroke($this->pdf);
    }

    public function drawRectangle($left, $top, $right, $bottom