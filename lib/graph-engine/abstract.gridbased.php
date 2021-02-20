<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

require_once 'lib/graph-engine/core.php';

class GridBasedGraphic extends Graphic
{
    public $dependant;
    public $independant;
    public $vertical;
    public $horizontal;

    public function __construct()
    {
        parent::__construct();
    }

    public function _getMinValue($type)
    {
        // Type is 'dependant' or 'independant'
        die("Abstract Function Call");
    }

    public function _getMaxValue($type)
    {
        // Type is 'dependant' or 'independant'
        die("Abstract Function Call");
    }

    public function _getLabels($type)
    {
        // Type is 'dependant' or 'independant'
        die("Abstract Function Call");
    }

    public function _drawContent(&$renderer)
    {
        $top = 0;
        $left = 0;
        $bottom = 1;
        $right = 1;

        $layout = $this->_layout();

        $this->_initScales($renderer, $layout, 'dependant');
        $this->_initScales($renderer, $layout, 'independant');
        $this->_drawScales($renderer, $layout, $left, $top, $right, $bottom);
        $this->_drawGridArea(new Fake_GRenderer($renderer, $left, $top, $right, $bottom), $layout);
    }

    public function _initScales(&$renderer, $layout, $type)
    {
        switch ($layout["grid-$type-scale"]) {
            case 'linear':
                $this->$type = new LinearGridScale($type, $layout, $this->_getMinValue($type), $this->_getMaxValue($type));
                break;
            case 'static':
                $this->$type = new StaticGridScale($type, $layout, $this->_getLabels($type));
                break;
        }

        // Setting the vertical or horizontal members to the same scale
        $ori = $this->$type->orientation;
        $this->$ori = &$this->$type;
    }

    public function _drawScales(&$renderer, $layout, &$left, &$top, &$right, &$bottom)
    {
        // Loop until scales are stable
        do {
            $otop = $top;
            $oleft = $left;
            $obottom = $bottom;
            $oright = $right;

            $size = $this->vertical->getSize($renderer, $bottom - $top);
            switch ($layout['grid-vertical-position']) {
                case 'left':
                    $left = $size;
                    break;
                case 'right':
                    $right = 1 - $size;
                    break;
            }

            $size = $this->horizontal->getSize($renderer, $right - $left);
            switch ($layout['grid-horizontal-position']) {
                case 'top':
                    $top = $size;
                    break;
                case 'bottom':
                    $bottom = 1 - $size;
                    break;
            }
        } while ($oleft != $left || $otop != $top || $oright != $right || $obottom != $bottom);

        switch ($layout['grid-vertical-position']) {
            case 'left':
                $this->vertical->drawScale(new Fake_GRenderer($renderer, 0, $top, $left, $bottom));
                break;
            case 'right':
                $this->vertical->drawScale(new Fake_GRenderer($renderer, $right, $top, 1, $bottom));
                break;
        }

        switch ($layout['grid-horizontal-position']) {
            case 'top':
                $this->horizontal->drawScale(new Fake_GRenderer($renderer, $left, 0, $right, $top));
                break;
            case 'bottom':
                $this->horizontal->drawScale(new Fake_GRenderer($renderer, $left, $bottom, $right, 1));
                break;
        }
    }

    public function _drawGridArea(&$renderer, $layout)
    {
        $renderer->drawRectangle(0, 0, 1, 1, $renderer->getStyle($layout['grid-