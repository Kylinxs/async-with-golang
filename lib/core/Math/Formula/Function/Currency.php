<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 * This Math function is a bit different than the rest.
 * It's purpose is to format the result of a calculation involving Tracker Currency
 * fields into a currency field. Example syntax:
 * (currency (cal-of-the-amount) (sourceCurrency) currencyFieldPermName)
 * sourceCurrency can be retrieved from currencyFieldPermName using this formula:
 * (substring currencyFieldPermName -3)
 */
class Math_Formula_Function_Currency extends Math_Formula_Function
{
    public function evaluate($element)
    {
        $amount = $this->evaluateChild($element[0]);
        $currency = $this->evaluateChild($element[1]);
        $currencyField = $element[2];

        $field = TikiLib::lib('trk')->get_field_by_perm_name($currencyField);

        if (empty($field)) {
            $this->error(tra('Missing currency field.'));
        }

        $factory = new Tracker_Field_Factory();
        $options = Tracker_Options::fromSerialized($field['options'], $factory->getFieldInfo($field['type']));

        if (! empty($element[3])) {
            $date = $this->evaluateChild($element[3]);
        } else {
            $date = null;
        }

        $smarty = TikiLib::lib('smarty');
        $smarty->loadPlugin('smarty_function_currency');
        return smarty_function_currency(
            [
                'amount' => $amount,
                'sourceCurrency' => $currency,
                'date' => $date,
                'prepend' => $options->getParam('prepend'),
                'append' => $options->getParam('append'),
                'locale' => $options->getParam('locale'),
                'defaultCurrency' => $options->getParam('currency'),
                'symbol' => $options->getParam('symbol'),
                'allSymbol' => $options->getParam('all_symbol'),
            ],
            $smarty->getEmptyInternalTemplate()
        );
    }
}
