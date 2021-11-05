
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$
// author : aris002@yahoo.co.uk
namespace TikiLib\Socnets\PrefsGen;

require_once('lib/socnets/Util.php');
use TikiLib\Socnets\Util\Util;

require_once('lib/socnets/LLOG.php');
//use TikiLib\Socnets\LLOG\LLOG;

use Feedback;
/**
* TODO speak with tiki developers about excessive preferences naming when in groups.
* Idea - group name?
*  I do not want naming noise with tpl but in search results names are too too short
*/
class PrefsGen
{
    public static $providersPath = 'vendor_bundled/vendor/hybridauth/hybridauth/src/Provider/*.php';

    //We are using socPreffix etc. with an easier testing and incorporating changes in mind
    //for extra socnets login libs and also maybe Packages in the future
    protected static string $socPreffix = 'socnets_';
    public static string $socLoginSuffix = 'tiki-login_hybridauth.php?provider=';
    public static string $socBaseSuffix = 'tiki-login_hybridauth.php';

    public static function getSocPreffix()
    {
        return self::$socPreffix;
    }


    public static function getSocBaseUrl()
    {
        global $base_url;
        return $base_url . self::$socBaseSuffix;
    }

    //just add provider name for the login and/or callback/redirect_uri
    public static function getPrefsSocLoginBaseUrl()
    {
        global $prefs;
        return $prefs[self::$socPreffix . 'socLoginBaseUrl'];
    }

    public static function getPrefsSocLoginUrl($providerName)
    {
        return self::getPrefsSocLoginBaseUrl() . $providerName;
    }

    public static function getSocLoginUrl($providerName)
    {
        global $base_url;
        return $base_url . self::$socLoginSuffix . $providerName;
    }

    public static function getSocLoginBaseUrl()
    {
        global $base_url;
        return  $base_url . self::$socLoginSuffix;
    }


    public static function getHybridProvidersPHP()
    {
        $ret = Util::getFileNamesPHP(self::$providersPath);
        if (count($ret) === 0) {
            Feedback::error('Socnets:' . tra('You do not have any providers. Have you installed hybridauth in ') . self::$providersPath . '?');
        }
        return $ret;
    }

  // eg 'socnet_facebook_loginEnabled' etc are formed by appending socnet_ AND _socnetname_ with each of the below
  // pre(su)fix _app_ is used to indicate data required to register with socnet
  //TODO fix settings after agreeing on setting dependancies
    public static function getBasePrefs()
    {
        //maybe later think about a DataCollection? Arrays not effective anymore in PHP?
        return [
            '_socnetEnabled' => [
                'name' => tra('socnet settings enabled?'),
                'description' => tra('socnet settings enabled by your website admins?'),
                'keywords' => 'social login',
                'type' => 'flag',
                'default' => 'n',
            ],
            '_loginEnabled' => [
                'name' => tra('login allowed?'),
                'description' => tra('socnet to login users into your website'),
                'keywords' => 'social login',
                'type' => 'flag',
                'dependencies' => [
                    '_socnetEnabled',
                    '_app_id',
                    '_app_secret',
                ],
                'default' => 'n',
            ],
            /*
            '_authType' => [
                'name' => tra('socnet login AuthType?'),
                'description' => tra('socnet login authentication type such as OAuth2 OAuth1 etc. Most websites now use more secure OAuth2'),
                'keywords' => 'social login',
                'type' => 'radio',
                'options' => [
                    'oauth2' => tra('OAuth2'),
                    'openidconnect' => tra('OpenIdConnect'),
                    'oauth' => tra('OAuth'),
                    'other auth' => tra('Other authentiction not implemented in tiki'),
                ],
                'default' => 'oauth2',
            ],
            */
            '_app_id' => [
                'name' => tra('Application ID'),
                'description' => tra('Application ID generated when registering this Tiki site as an application with them.'),
                'keywords' => 'social login',
                'type' => 'text',
                'size' => 100,
                'default' => '',
            ],
             '_app_secret' => [
                'name' => tra('Application secret'),
                'description' => tra('Application secret generated when registering this Tiki site as an application with them.'),
                'keywords' => 'social login',
                'type' => 'text',
                'size' => 100,
                'default' => '',
            ],
            '_app_api' => [
                'name' => tra('API (or Graph) version - NOT YET'),
                'description' => tra('socnets API (or Graph) version. Hybridauth default will be used until implemented'),
                'keywords' => 'social login',
                'type' => 'text',
                'size' => 30,
                'default' => '',
            ],
            '_app_site_name' => [
                'name' => tra('site name'),
                'description' => tra('The default website name that will be used by socnet for every web page. This parameter will be used instead of the browser title.'),
                'keywords' => 'social login',
                'type' => 'text',
                'size' => 60,
                'default' => '',
            ],
            '_app_site_image' => [
                'name' => tra('site image'),
                'description' => tra('The default image (logo, picture, etc) that will be used by socnet for every web page. The image must be specified as a URL or/and uploaded to socnet site.'),
                'keywords' => 'social login',
                'type' => 'text',
                'size' => 60,
                'default' => '',
            ],
            '_autocreateuser' => [
                'name' => tra('auto-create user?'),
                'description' => tra('automatically create a Tiki user by the username of fb_xxxxxxxx for eg users logging in using Facebook if they do not yet have a Tiki account. If not, they will be asked to link or register a Tiki account'),
                'keywords' => 'social networks',
                'type' => 'flag',
                'dependencies' => [
                    '_socnetEnabled',
                ],
                'default' => 'n',
            ],

            '_autocreate_prefix' => [
                'name' => tra('user prefix to auto-create'),
                'description' => tra('Tiki user prefix auto-creates. Eg xx_nnnnnnn etc. Press reset if there is none!'),
                'keywords' => 'social networks',
                'type' => 'text',
                'size' => 20,
                'dependencies' => [
                    '_socnetEnabled',
                    '_autocreateuser',
                ],
                'default' => 'soc_',
            ],
            '_autocreate_email' => [
                'name' => tra('auto-create user email?'),
                'description' => tra('automatically set a Tiki user email from socnet account.'),
                'keywords' => 'social networks',
                'type' => 'flag',
                'dependencies' => [
                    '_socnetEnabled',
                    '_autocreateuser',
                ],
                'default' => 'n',
            ],
            '_autocreate_user_trackeritem' => [
                'name' => tra('auto-create user tracker item?'),
                'description' => tra('automatically set a Tiki user tracker item from socnet account.'),
                'keywords' => 'social networks',
                'type' => 'flag',
                'dependencies' => [
                    '_socnetEnabled',
                    '_autocreateuser',
                ],
                'default' => 'n',
            ],
            '_autocreate_names' => [
                'name' => tra('auto-create user name(s)?'),
                'description' => tra('automatically set a Tiki user name/name from socnet account.'),
                'keywords' => 'social networks',
                'type' => 'flag',
                'dependencies' => [
                    '_socnetEnabled',
                    '_autocreateuser',
        //              '_autocreate_user_trackeritem',
                ],
                'default' => 'n',
            ]
        ];
    }


    public static function getOneSocPref($providerName, $key2, $value2)
    {
  /*
      if($key2 === '_authType') {
        $value2['name'] = 'What ' . ucfirst($key) ." " .$value2['name'];
        $value2['default'] = $value['authType'];
        $value2['_authType']['value'] = $value['authType'];
      }
      else if($key2 === 'graphVersion') {
        $value2['name'] = 'What ' . ucfirst($key) ." " .$value2['name'];
        $value2['default'] = $value['graphVersion'];
        $value2['_app_api']['value'] = $value['graphVersion'];
      }

    */
        if ($key2 === '_loginEnabled') {
            $value2['dependencies'][0] = self::$socPreffix . $providerName . $value2['dependencies'][0];
            $value2['dependencies'][1] = self::$socPreffix . $providerName . $value2['dependencies'][1];
            $value2['dependencies'][2] = self::$socPreffix . $providerName . $value2['dependencies'][2];
            $value2['description'] = 'Let ' . $providerName . " " . $value2['description'];
        } elseif ($key2 === '_autocreateuser') {
            $value2['dependencies'][0] = self::$socPreffix . $providerName . $value2['dependencies'][0];
            $value2['description'] = 'Let ' . $providerName . " " . $value2['description'];
        } elseif ($key2 === '_autocreate_prefix') {
            $value2['dependencies'][0] = self::$socPreffix . $providerName . $value2['dependencies'][0];
            $value2['dependencies'][1] = self::$socPreffix . $providerName . $value2['dependencies'][1];
            $value2['description'] = 'What ' . $providerName . " " . $value2['description'];
            $value2['default'] = (strlen($providerName) < 5) ? substr($providerName, 0, strlen($providerName)) . "_" : substr($providerName, 0, 4) . "_";
        } elseif (substr($key2, 0, 12) === '_autocreate_') {
            $value2['dependencies'][0] = self::$socPreffix . $providerName . $value2['dependencies'][0];
            $value2['dependencies'][1] = self::$socPreffix . $providerName . $value2['dependencies'][1];
            $value2['description'] = 'Let ' . $providerName . " " . $value2['description'];
        } else {
            $value2['description'] = $providerName . " " . $value2['description'];
        }

        $value2['name'] = $providerName . " " . $value2['name'];
    //  $value2['base_name'] = $key2;
    //  $value2['socnet_name'] = $providerName; //storing this way maybe is excessive... but maybe it is faster than extract it from the pref name?

        return $value2;
    }


    public static function getOneProviderPrefs($providerName, $socprefs)
    {
        $prefs2 = [];
        foreach ($socprefs as $key2 => $value2) {
                $p = self::getOneSocPref($providerName, $key2, $value2);
                $prefName = self::$socPreffix . $providerName . $key2;
                $prefs2[ $prefName ] = $p;
        }
        return $prefs2;
    }


    public static function getPrefsAllProviders()
    {
        $providers = self::getHybridProvidersPHP();

        $prefs3 = [];
        foreach ($providers as $providerName) {
            $socprefs = self::getBasePrefs();
            $prefs3 = array_merge($prefs3, self::getOneProviderPrefs($providerName, $socprefs));
        }
        return $prefs3;
    }


//TODO maybe test if it is faster to load settings for all socnets
// fix errors and add custom scopes
/*
    public static function getHybridauthConfig()
    {

        global $prefs;
        // Get Enabled providers!!!
        $providerNames = self::getEnabledProvidersNames();
        $loginUrl = Util::getSocUrl();

        Util::log2(' getHybridauthConfig providerNames:', $providerNames);

        $ret = [];

        foreach ($providerNames as $key => $name) {
            $ret[$name] =
                         [
                             'callback' => $loginUrl . $name,
                            'enabled' => $prefs[self::$socPreffix . $name . '_loginEnabled'],
                            'keys' => [
                    'id' => $prefs[self::$socPreffix . $name . '_app_id'],
                    'secret' => $prefs[self::$socPreffix . $name . '_app_secret'],
                        ],
    //          'scope' => $value['scope'],  //TODO TEST hybridauth reaction to : 'scope' => null or empty?
                ];
        }


        'sochybrid_config' = [
                'name' => tra('Hybridauth config'),
                'providers' => [], //prefs_sochybrid2_list(),
                 'debug_mode' => false,
                        // Path to file writeable by the web server. Required if 'debug_mode' is not false
                    'debug_file' => '/var/log/httpd/errors/tikihybrid.log',
        ],


        return $ret;

    }
*/
    //TODO check. I don't know how but it works.
    public static function getEnabledProvidersNames()
    {
        global $prefs;
        $ret = [];
        $socnets = self::getHybridProvidersPHP();
        foreach ($socnets as $name) {
            $prefName = self::$socPreffix . $name . '_socnetEnabled';
            if (isset($prefs[$prefName]) && $prefs[$prefName] === 'y') {
                $ret[] = $name;
            }
        }

        //Util::log2('get socnetsAll prefs:', $prefs[self::$socPreffix . 'socnetsAll'] );
        //Util::log2('getEnabledProvidersNames ret:', $ret );
        //Util::log2('getEnabledProviders prefs:', $prefs[self::$socPreffix . 'enabledProviders'] );

        return $ret;
    }

    //this is main PrefGen initialization. Kind of _construct() ;)
    public static function getSocPrefs($socprefix1)
    {
        self::$socPreffix = $socprefix1;

        Util::logclear();
        Util::log('getSocPrefs start');
        $allProviders = self::getHybridProvidersPHP();

        //Util::log2('socPreffix:', self::$socPreffix);
        //Util::deletePrefsStarts('sochybrid');

        $prefs1 = [
            self::$socPreffix . 'socnetsAll' => [
                'name' => tra('Enabled settings socnets:'),
                'description' => tra('Hybridauth enabled settings socnets'),
                'type' => 'multicheckbox',
                'options' => $allProviders,
                'default' => $allProviders,
                ],
                //TODO rename to remove confusion with loginEnabled
            self::$socPreffix . 'enabledProviders' => [
                'name' => tra('Enabled settings socnets:'),
                'description' => tra('Hybridauth enabled settings socnets'),
                'type' => 'multicheckbox',
                'options' => $allProviders,
                'default' => [],
                ],
            self::$socPreffix . 'enabledProvidersNames' => [
                'name' => tra('Enabled socnets Names- DO NOT USE in FORMS:'),
                'description' => tra('Hybridauth enabled socnets Names'),
                'type' => 'array',
                'default' => self::getEnabledProvidersNames(),
                'hidden' => 'y',
                //TODO does this array and hidden work? It looks like it is not...
                ],
            self::$socPreffix . 'socLoginBaseUrl' => [