<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 * PerspectiveLib
 *
 */
class PerspectiveLib
{
    private $perspectives;
    private $perspectivePreferences;

    /**
     *
     */
    public function __construct()
    {
        $this->perspectives = TikiDb::get()->table('tiki_perspectives');
        $this->perspectivePreferences = TikiDb::get()->table('tiki_perspective_preferences');
    }

    /**
     * @param $user
     * @return int
     */
    public function get_preferred_perspective($user)
    {
        $perspectiveId = null;

        if (empty($user)) {
            return $perspectiveId;
        }

        $sql = "SELECT value FROM tiki_user_preferences WHERE prefName='perspective_preferred' AND user=?";
        $perspectiveId = TikiDb::get()->getOne($sql, [$user]);

        if (is_numeric($perspectiveId)) {
            return (int) $perspectiveId;
        }

        return $perspectiveId;
    }


    /**
     * @param $prefs
     * @return int
     */
    public function get_current_perspective($prefs)
    {
        global $user;
        $tikilib = TikiLib::lib('tiki');
        $perspectiveId = $this->get_preferred_perspective($user);

        if (isset($_REQUEST['perspectiveId'])) {
            $perspectiveId = (int) $_REQUEST['perspectiveId'];
        } elseif (isset($_SESSION['current_perspective'])) {
            $perspectiveId = (int) $_SESSION['current_perspective'];
        }

        if ($perspectiveId) {
            return $perspectiveId;
        }

        if (method_exists($tikilib, "get_ip_address")) {
            $ip = $tikilib->get_ip_address();
        }

        foreach ($this->get_subnet_map($prefs) as $subnet => $perspective) {
            if ($this->is_in_subnet($ip, $subnet)) {
                return $perspective;
            }
        }

        $currentDomain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        foreach ($this->get_domain_map($prefs) as $domain => $perspective) {
            if ($domain == $currentDomain) {
                $_SESSION['current_perspective'] = trim($perspective);
                $_SESSION['current_perspective_name'] = $this->get_perspective_name($_SESSION['current_perspective']);
                return $perspective;
            }
        }
    }

    /**
     * @param $prefs
     * @param $active_pref
     * @param $config_pref
     * @return array
     */
    private function get_map($prefs, $active_pref, $config_pref)
    {
        if (! $prefs) {
            global $prefs;
        }

        $out = [];

        if (( ! empty($prefs[$active_pref]) && $prefs[$active_pref] != 'n' ) && isset($prefs[$config_pref])) {
            foreach (explode("\n", $prefs[$config_pref]) as $config) {
                if (substr_count($config, ',') == 1) {
                    // Ignore lines which don't have exactly one comma, such as empty lines.
                    // TODO: make sure there are no such lines in the first place
                    list($domain, $perspective) = explode(',', $config);
                    $out[$domain] = trim($perspective);
                }
            }
        }

        return $out;
    }

    /**
     * @param null $prefs
     * @return array
     */
    public function get_subnet_map($prefs = null)
    {
        return $this->get_map($prefs, 'site_terminal_active', 'site_terminal_config');
    }

    /**
     * @param null $prefs
     * @return array
     */
    public function get_domain_map($prefs = null)
    {
        return $this->get_map($prefs, 'multidomain_active', 'multidomain_config');
    }

    /**
     * @param $ip
     * @param $subnet
     * @return bool
     */
    private function is_in_subnet($ip, $subnet)
    {
        list($subnet, $size) = explode('/', $subnet);

        // Warning - bit shifting ahead.

        // Create the real mask from the /X suffix
        $mask = 0xFFFFFFFF ^ ((1 << (int) (32 - $size)) - 1);

        // Make sure the subnet-relevant part matches for the IP and the subnet being compared
        return (ip2long($subnet) & $mask) === (ip2long($ip) & $mask);
    }

    /**
     * Returns a string-indexed array containing the preferences for the given perspective as "pref_name" => "pref_value".
     *
     */
    public function get_preferences($perspectiveId)
    {
        $result = TikiDb::get()->query("SELECT pref, value FROM tiki_perspective_preferences WHERE perspectiveId = ?", [ $perspectiveId ]);

        $out = [];

        while ($row = $result->fetchRow()) {
            $out[ $row['pref'] ] = unserialize($row['value']);
        }

        return $out;
    }

    public function load_perspective_preferences()
    {
        global $prefs, $section;

        if (! isset($section) || $section != 'admin') {
            if ($persp = $this->get_current_perspective($prefs)) {
                $perspectivePreferences = $this->get_preferences($persp);
                $prefs = $perspectivePreferences + $prefs;
            }
        }

        return $prefs;
    }

    /**
     * @param $perspectiveId
     * @return mixed
     */
    public function get_perspective($perspectiveId)
    {
        $result = TikiDb::get()->query("SELECT perspectiveId, name FROM tiki_perspectives WHERE perspectiveId = ?", [ $perspectiveId ]);

        if ($info = $result->fetchRow()) {
            $perms = Perms::get([ 'type' => 'perspective', 'object' => $perspectiveId ]);
            if ($perms->perspective_view) {
                $info['preferences'] = $this->get_preferences($perspectiveId);
                $this->write_permissions($info, $perms);

                return $info;
            }
        }
    }


    /**
     * Changes the current perspective and redirects if multidomain_switchdomain enabled
     *
     * @param int $perspective  perspective id
     * @param bool $by_area     switched by the "areas" feature according to content, so keeps the same REQUEST_URI
     */
    public function set_perspective($perspective, $by_area = false)
    {
        global $prefs, $url_scheme, $user, $tikiroot;

        $preferred_perspective = $this->get_preferred_perspective($user);

        if (empty($perspective) && ! $preferred_perspective) {
            unset($_SESSION['current_perspective']);
            unset($_SESSION['current_perspective_name']);
        } else {
            $_SESSION['current_perspective'] = $perspective;
            $_SESSION['current_perspective_name'] = $this->get_perspective_name($_SESSION['current_perspective']);
        }

        if ($this->perspective_exists($perspective) || empty($perspective)) {
            if ($prefs['multidomain_switchdomain'] == 'y') {
                $perspectiveFound = false;
                $domainFound = false;
                foreach ($this->get_domain_map() as $domain => $persp) {
                    $domainFound = $domainFound || (isset($_SERVER['HTTP_HOST']) && $domain == $_SERVER['HTTP_HOST']);
                    if ($persp == $perspective) {
                        if (isset($_SERVER['HTTP_HOST']) && $domain != $_SERVER['HTTP_HOST']) {
                            $path = $tikiroot;
                            if ($by_area && ! empty($_SERVER['REQUEST_URI'])) {
                                $path = $_SERVER['REQUEST_URI'];
                            }
                            $targetUrl = $url_scheme . '://' . $domain . $path;

                            if ($prefs['feature_areas'] === 'y') {
                                header('HTTP/1.0 301 Found');
                            }
                            header('Location: ' . $targetUrl);
                            exit;
                        }
                        $perspectiveFound = true;
                        break;
                    }
                }
                if (! $perspectiveFound && $domainFound) {
                    $accesslib = TikiLib::lib('access');
                    if (! empty($prefs['multidomain_default_not_categorized'])) {
                        if ($prefs['multidomain_default_not_categorized'] != $_SERVER['HTTP_HOST']) {
                            $saveHttpHost = $_SERVER['HTTP_HOST'];
                            // selfUrl uses HTTP_HOST, and redirect will exit after redirect, so no problem on "tampering" with $_SERVER
                            $_SERVER['HTTP_HOST'] = $prefs['multidomain_default_not_categorized'];
                            $accesslib->redirect($accesslib->selfUrl(), '', 301);
                            $_SERVER['HTTP_HOST'] = $saveHttpHost; // this should never be reach
                        }
                    } else {
                        $accesslib->display_error(
                            $accesslib->selfUrl(),
                            tra("Perspective misconfiguration"),
                            500,
                            false,
                            tra('The resource you requested is not available in the current perspective, and the system administrator did not define a default domain to redirect to. Please contact your system administrator, or check the documentation in <a href="http://doc.tiki.org/Perspectives">http://doc.tiki.org/Perspectives</a> related with multi-domain configurations.')
                        );
                    }
                }
            }
        }
    }


    /**
     * @param $info
     * @param $perms
     */
    private function write_permissions(&$info, $perms)
    {
        $info['can_edit'] = $perms->perspective_edit;
        $info['can_remove'] = $perms->perspective_admin;
        $info['can_perms'] = $perms->perspective_admin;
    }

    /**
     * Adds or renames a perspective. If $perspectiveId exists, rename it to $name.
     * Otherwise, create a new perspective with id $perspectiveId named $name.
     * Returns true if and only if the operation succeeds.
     *
     */
    public function replace_perspective($perspectiveId, $name)
    {
        if ($perspectiveId) {
            $this->perspectives->update(
                ['name' => $name,],
                ['perspectiveId' => $perspectiveId,]
            );

            return $perspectiveId;
        } else {
            return $this->perspectives->insert(['name' => $name,]);
        }
    }

    /**
     * Removes a perspective
     *
     */
    public function remove_perspective($perspectiveId)
    {
        if ($perspectiveId) {
            $this->perspectives->delete(['perspectiveId' => $perspectiveId]);
            $this->perspectivePreferences->deleteMultiple(['perspectiveId' => $perspectiveId]);
        }
    }

    /**
     * Replaces all preferences from $perspectiveId with those in the provided string-indexed
     *   array (in format "pref_name" => "pref_value").
     *
     */
    public function replace_preferences($perspectiveId, $preferences)
    {
        $this->perspectivePreferences->deleteMultiple(['perspectiveId' => $perspectiveId]);

        $prefslib = TikiLib::lib('prefs');
        foreach ($preferences as $pref => $value) {
            $value = $prefslib->formatPreference($pref, [$pref => $value]);
            $this->set_preference($perspectiveId, $pref, $value);
        }
    }

    /**
     * Replaces a specific preference
     *
     */
    public function replace_preference($preference, $value, $newValue)
    {
        $this->perspectivePreferences->update(
            ['value' => serialize($newValue),],
            [
                'pref' => $preference,
                'value' => serialize($value),
            ]
        );
    }

    /**
     * Sets $preference's value for $perspectiveId to $value
     *
     */
    public function set_preference($perspectiveId, $preference, $value)
    {
        $this->perspectivePreferences->delete(
            [
                'perspectiveId' => $perspectiveId,
                'pref' => $preference,
            ]
        );

        $this->perspectivePreferences->insert(
            [
                'perspectiveId' => $perspectiveId,
                'pref' => $preference,
                'value' => serialize($value),
            ]
        );
    }

    /**
     * Returns true if and only if a perspective with the given $perspectiveId exists
     *
     */
    public function perspective_exists($perspectiveId)
    {
        $db = TikiDb::get();

        $id = $db->getOne(
            'SELECT perspectiveId FROM tiki_perspectives WHERE perspectiveId = ?',
            [ $perspectiveId ]
        );

        return ! empty($id);
    }

    /**
     * @param int $offset
     * @param $maxRecords
     * @return array
     */
    public function list_perspectives($offset = 0, $maxRecords = -1)
    {
        $db = TikiDb::get();

        $list = $db->fetchAll("SELECT perspectiveId, name FROM tiki_perspectives", [], $maxRecords, $offset);

        $list = Perms::simpleFilter('perspective', 'perspectiveId', 'perspective_view', $list);

        foreach ($list as & $info) {
            $perms = Perms::get([ 'type' => 'perspective', 'object' => $info['perspectiveId'] ]);
            $this->write_permissions($info, $perms);
        }

        return $list;
    }

    /**
     * Returns one of the perspectives with the given name
     *
     */
    public function get_perspective_with_given_name($name)
    {
        $db = TikiDb::get();

        return $db->getOne("SELECT perspectiveId FROM tiki_perspectives WHERE name = ?", [ $name ]);
    }

    /**
     * Returns perspective's name from the Id
     *
     */
    public function get_perspective_name($id)
    {
        $db = TikiDb::get();

        return $db->getOne("SELECT name FROM tiki_perspectives WHERE perspectiveId = ?", [ $id ]);
    }
}
