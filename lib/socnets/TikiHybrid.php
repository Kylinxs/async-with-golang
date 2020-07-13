
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id:

//author : aris002@yahoo.co.uk

namespace TikiLib\Socnets\TikiHybrid;

require_once('lib/socnets/Util.php');
use TikiLib\Socnets\Util\Util;
require_once('lib/socnets/LLOG.php');
//use TikiLib\Socnets\LLOG\LLOG;
require_once('lib/socnets/PrefsGen.php');
use TikiLib\Socnets\PrefsGen\PrefsGen;

use Hybridauth\Logger\LoggerInterface;
use Hybridauth\Logger;
use Hybridauth\Exception\Exception;
use Hybridauth\Hybridauth;
use Hybridauth\HttpClient;

use TikiLib;
use Feedback;
use Tracker_Definition;
use Services_Tracker_Utilities;
use Tiki\Lib\Logs\LogsLib;


class TikiHybrid extends LogsLib
{
    //TODO when for strings assign null or when ''? or don't need anything?
    protected static string $socPreffix = 'socnets_';
    protected string $namedpreffix = '';
    protected string $providerName = '';
    public $adapter = null;

    public $logger = null;
    public static string $logpath = '/var/log/httpd/errors/tikihybrid.log';


    public function __construct($providerName)
    {
        try {
            global $prefs;
            $this -> providerName = $providerName;
            self::$socPreffix = PrefsGen::getSocPreffix();
            $this->namedprefix = self::$socPreffix . $providerName;
            Util::log2('tikiHybrid  constructor namedprefix:', $this -> namedprefix);


            //we need to use Guzzle due to errors with curl!
            $guzzle = new HttpClient\Guzzle(null, [
                 'verify' => false # Set to false to disable SSL certificate verification
            ]);
            //maybe later for testing to separate logs for each provider?
            //$this->logger = new Logger\Logger(true, self::$logpath);

            //Feedback::warning(Util::getLogFile());


            if (! $this->isRegistered()) {
                Feedback::error(tr('TikiHybrid __construct: this site is not registered with ' . $this->$providerName));
                // do we need to land on tiki-admin.php?
                header('Location: tiki-index.php');
    //          header('Location: tiki-admin.php'); //?page=socialnetworks');
                die();
            }


            $this -> config =
            [
            'callback' => PrefsGen::getPrefsSocLoginBaseUrl() . $providerName,
            'enabled' => $prefs[$this->namedprefix . '_loginEnabled'],
            'keys' => [
                'id' => $prefs[$this->namedprefix . '_app_id'],
                'secret' => $prefs[$this->namedprefix . '_app_secret'],
            ],
            //'scope' => $value['scope'],  //TODO TEST hybridauth reaction to : 'scope' => null or empty?
            ];


    //  LLOG('tikiHybrid  constructor config:', $this -> config );

            $adapterClass = '\\Hybridauth\\Provider\\' . $providerName;

            $this -> adapter = new $adapterClass($this -> config, $guzzle);

        //$this->hybridauth = new Hybridauth($confhybrid, $guzzle, null, $this->logger );
        //  LLOG('tikiHybrid  constructed :)' );
        } catch (Throwable $e) {
            error_log($e->getMessage());
            //echo TikiHybrid construct error:' . $e->getMessage();
            Feedback::error('TikiHybrid construct error: ' . $e->getMessage());
        }
    }



    // we might send just hybridauth adapter after testing
    public function login()
    {
        global $prefs, $user;
        $userlib = TikiLib::lib('user');



        try {
            $tokens = $this->adapter->getAccessToken();
            $accessToken = $tokens['access_token'];
            $userProfile = $this->adapter->getUserProfile();

            $userId = $userProfile->identifier;
            //email not needed here
            //$email = $userProfile->email;
        //  LLOG('login email=', $email);
        //  LLOG('login tokens=', $tokens);
        //  LLOG('login accessToken=', $accessToken);

            //TODO remove after tests!!!
            //Feedback::note("socnet_id : " . $socnet_id);
            //Feedback::note("namedprefix : " . $namedprefix);

            if (! $user) {
                //TODO do we need to test this here? Can someone suddenly dissable login even if
                //we checked this at the begining?
                if ($prefs[$this->namedprefix . '_loginEnabled'] != 'y') {
                    Feedback::error(tra('Login to this site using ' . $this->providerName . ' is disabled. Contact admin.'));
                    return false;
                }
//this one is slow. It would be good to make it faster. Need to find out how.
//$local_user = $this->getOne("select `user` from `tiki_user_preferences` where `prefName` = ? and `value` = ?", [ 'facebook_id', $socnet_id]);
                $local_user = $this -> getOne("select `user` from `tiki_user_preferences` where `prefName` = ? and `value` = ?", [$this->namedprefix . '_id', $userId]);

            //  Feedback::note('local_user: '. $local_user);


                if ($local_user) {
                    $user = $local_user;
                } elseif ($prefs[$this->namedprefix . '_autocreateuser'] === 'y') {
                    //Feedback::error( tra('Creating new users is disabled for ' . $providerName ));
                    $local_user = $this->createUser($userProfile);
                }

                if ($local_user) {
                    $user = $local_user;
                } else {
                    Feedback::error(tra('You need to link your local account to ' . $this->providerName . '  before you can login using it'));
                    header('Location: tiki-index.php');
                    die;
                }

                global $user_cookie_site;
                $_SESSION[$user_cookie_site] = $user;
                $userlib->update_expired_groups();
                $this->set_user_preference($user, $this->namedprefix . '_id', $userId);
                $this->set_user_preference($user, $this->namedprefix . '_token', $accessToken);
                $userlib->update_lastlogin($user);

                //TODO photoURL works for facebook and google but need to test for other socnets
                $photoURL = strtok($userProfile->photoURL, '?') . '?access_token=' . $accessToken;
                //  LLOG('login photoURL=' . $photoURL);
                //  LLOG('createUser start accesToken=' , $accessToken);

                $avatarlib = TikiLib::lib('avatar');
                $avatarlib->set_avatar_from_url($photoURL, $user);


                Feedback::note('Welcome, ' . $userProfile->firstName . ' ' . $userProfile->lastName . '!');
                //TODO save visitors page and to land there?
                header('Location: tiki-index.php');
                die;
            } else { //relogin if logged in?
                $this->set_user_preference($user, $this->namedprefix . '_id', $userId);
                $this->set_user_preference($user, $this->namedprefix . '_token', $accessToken);
            }
        } catch (Throwable $e) {
            Feedback::error('TikiHybrid login error: ' . $e->getMessage());
        }
    }

    /**
 * Checks if the site is registered with a social network like facebook etc. (application id , api key and secret are set)
 *
 * @return bool true, if this site is registered with a social network like facebook etc.
 */
    private function isRegistered()
    {
        //if ($name === "" or not in the list?)
        //TODO add "is enabled"? and maybe is there a better test?
        global $prefs;
        return ($prefs[$this -> namedprefix . '_app_id'] != '' && $prefs[$this -> namedprefix . '_app_secret'] != '');
    }


    /**
     * Creates a new tiki user from a socnet user profile
     *
     * @returns $user it created
     */
    //TODO save newly created users token!
    private function createUser($userProfile)
    {
        global $prefs, $user;
        $userlib = TikiLib::lib('user');


        if ($prefs[$this->namedprefix . '_autocreateuser'] != 'y') {
            Feedback::error(tr('TikiHybrid is not allowed to create a new user with your ' . $this->providerName . ' account. Please contact administrator.'));
            return $user;
        }

        $userId = $userProfile->identifier;
        $email = $userProfile->email;
    //  LLOG('createUser start email=', $email);

        $firstName = $userProfile->firstName;
        $lastName = $userProfile->lastName;

        //  $email = $prefs[$namedprefix . '_autocreate_userFromEmail'] == 'y' ? $userProfile->email : '';
        //  TODO we need change to _autocreate_userFromEmail for the sake of clarity?
        $autoEmail = $prefs[$this->namedprefix . '_autocreate_email'] === 'y' ? $email : '';
    //  LLOG('createUser autoEmail=',  $autoEmail);

        // IMPORTANT TODO test user creation below line is true!
        $autoUser = $prefs['login_is_email'] === 'y' ? $autoEmail : '';
        $autoPrefixedId = $prefs[ $this->namedprefix . '_autocreate_prefix'] . $userId;

        $randompass = $userlib->genPass();

    //  LLOG('createUser autoUser=',  $autoUser);

        if ($autoUser) {
            $user = $userlib->add_user($autoUser, $randompass, $email);
        } else {
            $user = $userlib->add_user($autoPrefixedId, $randompass, $email);
        }

//          Feedback::error(tr('TikiHybrid is asked to link your account with: ' . $providerName . ' But it is not implemented yet in Tiki.'));


        if (! $user) {
            $err_msg = tr('TikiHybrid unable to create a new user:' . $user . ' with your ' . $this->providerName . ' account. You might already have an account and need to link. But it is not implemented yet.');
            //error_log($err_msg);
            Feedback::error($err_msg);
        } else {
            Feedback::note('TikiHybrid from ' . $this->providerName . ' has created a new user: ' . $user);
        }

        //TODO check if user trackers work correctly
        $ret = $userlib->get_usertrackerid("Registered");
        $userTracker = $ret['usersTrackerId'];
        $userField = $ret['usersFieldId'];
        $isAutoNames = $prefs[$this->namedprefix . '_autocreate_names'];

        if ($prefs[$this->namedprefix . '_autocreate_user_trackeritem'] === 'y' && $userTracker && $userField) {
            $definition = Tracker_Definition::get($userTracker);
            $utilities = new Services_Tracker_Utilities();
            $fields = ['ins_' . $userField => $user];
            //TODO check this! it was ! empty($autoNames)
            //tracker items
            $autoNames = ''; //[2,3]; //??

            if ($isAutoNames === 'y') {
                $names = array_map('trim', explode(',', $autoNames));
                $fields['ins_' . $names[0]] = $firstName;
                $fields['ins_' . $names[1]] = $lastName;
            }

            $utilities->insertItem(
                $definition,
                [
                    'status' => '',
                    'fields' => $fields,
                    'validate' => false,
                ]
            );
        }

        $this->set_user_preference($user, 'realName', $firstName . ' ' . $lastName);

//      if ($prefs['socialnetworks_facebook_firstloginpopup'] == 'y') {
//          $this->set_user_preference($user, 'socialnetworks_user_firstlogin', 'y');
//      }
//      if ($prefs['feature_userPreferences'] == 'y') {
//          $fb_avatar = json_decode($this->facebookGraph('', 'me/picture', ['type' => 'square', 'width' => '480', 'redirect' => '0','access_token' => $access_token], false, 'GET'));

//  }

        return $user;
    }




    //this when Hybridauth class is used.
    public function checkConnectedAdapters()
    {
        $adapters = $this->hybridauth->getConnectedAdapters();
        if ($adapters) {
            Feedback::warning('TikiHybrid has connected adapters: ' . count($adapters));
        } else {
            Feedback::warning('TikiHybrid has NO connected adapters.');
        }
    }
}