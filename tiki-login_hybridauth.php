<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

//author : aris002@yahoo.co.uk


$section = 'mytiki';
require_once('tiki-setup.php');
//require_once('lib/prefs/socnets.php');

require_once('lib/socnets/Util.php');
use TikiLib\Socnets\Util\Util;
require_once('lib/socnets/LLOG.php');

//$access->check_feature('feature_socialnetworks');
//$access->check_permission('tiki_p_socialnetworks', tra('Social networks'));

require_once('lib/socnets/TikiHybrid.php');
use TikiLib\Socnets\TikiHybrid\TikiHybrid;

$auto_query_args = [];

use Hybridauth\Storage\Session;

$providerName = '';
$adapter = null;
$tikihybridi = null;
Util::logclear();



try {
    $storage = new Session();
    $error = false;

    //
    // Event 1: User clicked SIGN-IN link
    //
    //if (isset($_REQUEST['provider'])) //TODO some say it is not safe?
    if (isset($_GET['provider'])) {
        $provider = $_GET['provider'];
        //TODO Validate here provider exists in the $prefs?
        $storage->set('provider', $provider);
        $tikihybridi = new TikiHybrid($provider);

        LLOG('login GET provider=', $provider);

        //header('Location: tiki-index.php');
        //die;
    }

    //
    // Event 2: Provider returns via CALLBACK
    //
    if ($provider = $storage->get('provider')) {
        LLOG('Provider returns via CALLBACK storage provider:', $provider);
        $tikihybridi->adapter->authenticate();

        $storage->set('provider', null);

        $tikihybridi->login();
        $tikihybridi->adapter->disconnect();
        $tikihybridi = null;
    }


    LLOG('aris002 COMMON END  /////////////////////////');
} catch (Throwable $e) {
    error_log($e->getMessage());
    echo $e->getMessage();
    Feedback::error('TikiHybrid provider error: ' . $e->getMessage());
}

/*
//Do we still need this for tiki.tpl?
if ($user) {
    $token = $tikilib->get_user_preference($user, 'socnets_' . $providerId . '_token', '');
    Feedback::warning("User exists");
    $smarty->assign('socnets_' . $providerName, ($token != ''));
}
*/
//LLOG('writing adapter =null');


//ask_ticket('socialnetworks');  //What does this do here?

// disallow robots to index page:
$smarty->assign('metatag_robots', 'NOINDEX, NOFOLLOW');
$smarty->display("tiki.tpl");
