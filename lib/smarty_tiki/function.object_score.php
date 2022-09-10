
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

function smarty_function_object_score($params, $smarty)
{
    extract($params);
    if (empty($id) || empty($type)) {
        trigger_error("object_score: missing id and/or type parameters");
        return;
    }
    $scorelib = TikiLib::lib("score");

    if (! empty($ruleId)) {
        return $scorelib->getPointsBalanceForRuleId($type, $id, $ruleId);
    } elseif ($grouped == 'y') {
        $scoreArr = $scorelib->getGroupedPointsBalance($type, $id);
        if (empty($assign)) {
            return $scoreArr;
        } else {
            $smarty->assign($assign, $scoreArr);
        }
    } else {
        return $scorelib->getPointsBalance($type, $id);
    }
}