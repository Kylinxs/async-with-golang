<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 * "Side-by-Side" diff renderer.
 *
 * This class renders the diff in "side-by-side" format, like Wikipedia.
 *
 * @package Text_Diff
 */
class Text_Diff_Renderer_character extends Tiki_Text_Diff_Renderer
{
    protected $orig;
    protected $final;

    public function __construct($context_lines = 0)
    {
        $this->_leading_context_lines = $context_lines;
        $this->_trailing_context_lines = $context_lines;
        $this->orig = "";
        $this->final = "";
    }

    protected function _startDiff()
    {
    }

    protected function _endDiff()
    {
        return [$this->orig, $this->final];
    }

    protected function _blockHeader($xbeg, $xlen, $ybeg, $ylen)
    {
    }

    protected function _startBlock($header)
    {
        echo $header;
    }

    protected function _endBlock()
    {
    }

    protected function _lines($lines, $prefix = '', $suffix = '', $type = '')
    {
        if ($type == 'context') {
            foreach ($lines as $line) {
                $this->orig .= htmlspecialchars($line);
                $this->final .= htmlspecialchars($line);
            }
        } elseif ($type == 'added' || $type == 'change-added') {
            $l = "";
            foreach ($lines as $line) {
                $l .= htmlspecialchars($line);
            }
            if (! empty($l)) {
                $this->final .= '<ins class="diffchar inserted"><strong>' . $l . "</strong></ins>";
            }
        } elseif ($type == 'deleted' || $type == 'change-deleted') {
            $l = "";
            foreach ($lines as $line) {
                $l .= htmlspecialchars($line);
            }
            if (! empty($l)) {
                $this->orig .= '<del class="diffchar deleted"><strong>' . $l . "</strong></del>";
            }
        }
    }

    protected function _context($lines)
    {
        $this->_lines($lines, '', '', 'context');
    }

    protected function _added($lines, $changemode = false)
    {
        if ($changemode) {
            $this->_lines($lines, '+', '', 'change-added');
        } else {
            $this->_lines($lines, '+', '', 'added');
        }
    }

    protected function _deleted($lines, $changemode = false)
    {
        if ($changemode) {
            $this->_lines($lines, '-', '', 'change-deleted');
        } else {
            $this->_lines($lines, '-', '', 'deleted');
        }
    }

    protected function _changed($orig, $final)
    {
        $this->_deleted($orig, true);
        $this->_added($final, true);
    }
}
