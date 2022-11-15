
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$
use Tiki\TikiInit;

/**
 *
 */
class MailinLib extends TikiDb_Bridge
{
    public function list_available_types()
    {
        $container = TikiInit::getContainer();
        $list = $container->get('tiki.mailin.providerlist');

        $out = [];
        foreach ($list->getList() as $provider) {
            $out[$provider->getType()] = [
                'name' => $provider->getLabel(),
                'enabled' => $provider->isEnabled(),
            ];
        }

        return $out;
    }

    /**
     * @param $offset
     * @param $maxRecords
     * @param $sort_mode
     * @param $find
     * @return array
     */
    public function list_mailin_accounts($offset, $maxRecords, $sort_mode, $find)
    {

        if ($find) {
            $findesc = '%' . $find . '%';
            $mid = " where `account` like ?";
            $bindvars = [$findesc];
        } else {
            $mid = "    ";
            $bindvars = [];
        }

        $query = "select * from `tiki_mailin_accounts` $mid order by " . $this->convertSortMode($sort_mode);
        $query_cant = "select count(*) from `tiki_mailin_accounts` $mid";
        $result = $this->query($query, $bindvars, $maxRecords, $offset);
        $cant = $this->getOne($query_cant, $bindvars);
        $ret = [];

        while ($res = $result->fetchRow('DB_FETCHMODE_ASSOC')) {
            // Decrypt the password
            $pwd = $this->decryptPassword($res['pass']);
            $res['pass'] = $pwd;

            $ret[] = $res;
        }

        $retval = [];
        $retval["data"] = $ret;
        $retval["cant"] = $cant;
        return $retval;
    }

    /**
     * @param $offset
     * @param $maxRecords
     * @param $sort_mode
     * @param $find
     * @return array
     */
    public function list_active_mailin_accounts($offset, $maxRecords, $sort_mode, $find)
    {

        if ($find) {
            $findesc = '%' . $find . '%';
            $mid = " where `active`=? and `account` like ?";
            $bindvars = ["y",$findesc];
        } else {
            $mid = " where `active`=?";
            $bindvars = ["y"];
        }

        $query = "select * from `tiki_mailin_accounts` $mid order by " . $this->convertSortMode($sort_mode);
        $query_cant = "select count(*) from `tiki_mailin_accounts` $mid";
        $result = $this->query($query, $bindvars, $maxRecords, $offset);
        $cant = $this->getOne($query_cant, $bindvars);
        $ret = [];

        while ($res = $result->fetchRow('DB_FETCHMODE_ASSOC')) {
            // Decrypt the password
            $pwd = $this->decryptPassword($res['pass']);
            $res['pass'] = $pwd;

            $ret[] = $res;
        }

        $retval = [];
        $retval["data"] = $ret;
        $retval["cant"] = $cant;
        return $retval;
    }

    /**
     * replace_mailin_account
     *
     * @param  mixed $accountId
     * @param  mixed $account
     * @param  mixed $protocol
     * @param  mixed $host
     * @param  mixed $port
     * @param  mixed $username
     * @param  mixed $clearpass
     * @param  mixed $type
     * @param  mixed $active
     * @param  mixed $anonymous
     * @param  mixed $admin
     * @param  mixed $attachments
     * @param  mixed $tracker_attachments
     * @param  mixed $routing
     * @param  mixed $article_topicId
     * @param  mixed $article_type
     * @param  mixed $discard_after
     * @param  mixed $show_inlineImages
     * @param  mixed $save_html
     * @param  mixed $categoryId
     * @param  mixed $namespace
     * @param  mixed $respond_email
     * @param  mixed $leave_email
     * @param  mixed $galleryId
     * @param  mixed $trackerId
     * @param  mixed $preferences
     * @return bool
     */
    public function replace_mailin_account($accountId, $account, $protocol, $host, $port, $username, $clearpass, $type, $active, $anonymous, $admin, $attachments, $tracker_attachments, $routing, $article_topicId = null, $article_type = null, $discard_after = null, $show_inlineImages = 'n', $save_html = 'y', $categoryId = 0, $namespace = '', $respond_email = 'y', $leave_email = 'n', $galleryId = null, $trackerId = null, $preferences = null)
    {
        // Fix values
        if ($attachments == null) {
            $attachments = 'n';
        }

        // if account is Store Mail in Tracker
        if ($type == 'tracker') {
            $attachments = $tracker_attachments;
        } else {
            $trackerId = null;
        }

        $preferences_ = json_decode($preferences);
        // check the links
        if ($preferences_->error || count($preferences_->links) == 0 || $trackerId == 0) {
            $preferences = null;
        }

        $data = [
            'account' => $account,
            'protocol' => $protocol,
            'host' => $host,
            'port' => (int) $port,
            'username' => $username,
            'type' => $type,
            'active' => $active,
            'anonymous' => $anonymous,
            'admin' => $admin,
            'attachments' => $attachments,
            'routing' => $routing,
            'article_topicId' => (int) $article_topicId,
            'article_type' => $article_type,
            'discard_after' => $discard_after,
            'show_inlineImages' => $show_inlineImages,
            'save_html' => $save_html,
            'categoryId' => (int) $categoryId,
            'namespace' => $namespace,
            'respond_email' => $respond_email,
            'leave_email' => $leave_email,
            'galleryId' => $galleryId,
            'trackerId' => $trackerId,
            'preferences' => $preferences
        ];

        if ($clearpass) {
            // Encrypt password
            $data['pass'] = $this->encryptPassword($clearpass);
        }

        $table = $this->table('tiki_mailin_accounts');
        if ($accountId) {
            $table->update($data, [
                'accountId' => $accountId,
            ]);

            return $accountId;
        } else {
            return $table->insert($data);
        }
    }

    /**
     * @param $accountId
     *
     * @return TikiDb_Pdo_Result|TikiDb_Adodb_Result
     */
    public function remove_mailin_account($accountId)
    {
        $query = "delete from `tiki_mailin_accounts` where `accountId`=?";
        return $this->query($query, [(int)$accountId]);
    }

    /**
     * @param $accountId
     *
     * @return array|bool
     */
    public function get_mailin_account($accountId)
    {
        $query = "select * from `tiki_mailin_accounts` where `accountId`=?";
        $result = $this->query($query, [(int)$accountId]);
        if (! $result->numRows()) {
            return false;
        }
        $res = $result->fetchRow('DB_FETCHMODE_ASSOC');

        // Decrypt the password
        $pwd = $this->decryptPassword($res['pass']);
        $res['pass'] = $pwd;

        return $res;
    }

    /**
     * encryptPassword the email account password
     *
     * @param string $pwd Password in clear-text
     * @return crypt Encoded password
     *
     */
    public function encryptPassword($pwd)
    {
        $encoded = base64_encode($pwd);
        return $encoded;
    }

    /**
     * decryptPassword the email account password
     *
     * @param crypt $$encrypted Encoded password
     * @return string Return clear text password
     *
     */
    public function decryptPassword($encoded)
    {
        $plaintext = base64_decode($encoded);
        return $plaintext;
    }

    /**
     * checkPackage
     *
     * @return string
     */
    public function checkPackage()
    {
        $status = "n";
        if (file_exists('vendor_bundled/vendor/philippemarcmeyer/fieldslinker/')) {
            $status = "y";
        }
        return $status;
    }
}