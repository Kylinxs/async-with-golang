<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tracker\Filter\Control;

interface Control
{
    /**
     * Collect the input values for the controlled field.
     */
    public function applyInput(\JitFilter $input);

    /**
     * Provide the portion of the query arguments relating to this field.
     * Will be used to generate links.
     *
     * Provided as a map to be handled by http_build_query()
     */
    public function getQueryArguments();

    /**
     * Provide a textual description of the filter being applied.
     * Return null when unapplied.
     */
    public function getDescription();

    /**
     * Provide the ID of the primary field to be referenced by the label.
     */
    public function getId();

    /**
     * Determines if the control is usable.
     */
    public function isUsable();

    /**
     * Determines if the control has a selected value.
     */
    public function hasValue();

    /**
     * Render the field within a form.
     */
    public function __toString();
}
