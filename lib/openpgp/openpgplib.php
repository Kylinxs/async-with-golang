<?php

/////////////////////////////////////////////////////////////////////////////
// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
//
// PURPOSE:
// A brief OpenPGP support class for Tiki OpenPGP functionality in
//  - webmail
//  - ZF based mail
//  - newsletters
//  - admin notifications
//
//
//
// CHANGE HISTORY:
// v0.10
// 2012-11-04   hollmeer: Collected all functions into intial version openpgplib.php.
//      Minimal preparation/calling portions remain in caller sources,
//      as it seems so far adequate with current approch to leave
//      such portions e.g. there
//      NOTE: Zend Framework as is wrapped by bringing/changing
//            necessary classes from
//              Zend/Mail/ and
//              Zend/Mail/Transport/
//            into lib/openpgp/ per now. No patches needed anymore
//            into ZF to enable 100% PGP/MIME encryption.
// v0.11
// 2014-11-04   hollmeer: Protected function naming to _xxxx
// v0.12
// 2014-12-01   hollmeer: Changed all OpenGPG functionality configuration to use
//      preferences
//
//
//
/////////////////////////////////////////////////////////////////////////////


//this script may only be included - so its better to die if called directly.
if (strpos($_SERVER["SCRIPT_NAME"], basename(__FILE__)) !== false) {
    header("location: index.php");
    exit;
}

class OpenPGPLib
{
    //PGP/MIME HEADER CONSTANTS
    const MULTIPART_PGP_ENCRYPTED = 'multipart/encrypted';
    const TYPE_PGP_PROTOCOL = 'application/pgp-encrypted';
    const PGP_MIME_NOTE = 'This is an OpenPGP/MIME encrypted message (RFC 2440 and 3156)';
    const TYPE_PGP_CONTENT_VERSION = 'application/pgp-encrypted';
    const DESCRIPTION_PGP_CONTENT_VERSION = 'PGP/MIME version identification';
    const PGP_MIME_VERSION_IDENTIFICATION = 'Version: 1';
    const TYPE_PGP_CONTENT_ENCRYPTED = 'application/octet-stream; name="encrypted.asc"';
    const DESCRIPTION_PGP_CONTENT_ENCRYPTED = 'OpenPGP encrypted message';
    const DISPOSITION_PGP_CONTENT_INLINE = 'inline; filename="encrypted.asc"';

    /**
     * EOL character string used by transport
     * @var string
     * @access public
     */
    private $EOL = "\n";


    /**
    * Full path to gpg
    * @var string
    * @access protected
    */
    private $_gpg_path;

    /**
    * Full path to keyring directory
    * @var string
    * @access protected
    */
    private $_gpg_home;

    /**
    * gpg signer idfile
    * @var string
    * @access protected
    */
    private $_gpg_sgn_id;

    /**
    * gpg signer passphrase
    * @var string
    * @access protected
    */
    private $_gpg_sgn_passphrase;

    /**
    * gpg signer full passfile path
    * @var string
    * @access protected
    */
    private $_gpg_sgn_passfile_path;

    /**
    * gpg trust
    * depending on which version of GnuPG we're using there
    * are two different ways to specify "always trust"
    * @var string
    * @access protected
    */
    private $_gpg_trust;

    /**
    * Constructor function. Set initial defaults.
    */
    public function __construct()
    {
        global $prefs,$tiki_p_admin;

        $this->_gpg_path = $prefs['openpgp_gpg_path'];
        $this->_gpg_home = $prefs['openpgp_gpg_home'];
        $this->_gpg_sgn_id = $prefs['sender_email'];
        if ($prefs['openpgp_gpg_signer_passphrase_store'] == 'file') {
            $this->_gpg_sgn_passfile_path = $prefs['openpgp_gpg_signer_passfile'];
            $this->_gpg_sgn_passphrase = '';
        } else {
            $this->_gpg_sgn_passfile_path = '';
            $this->_gpg_sgn_passphrase = $prefs['openpgp_gpg_signer_passphrase'];
        }
        $this->_gpg_trust = '';

        $this->setCrlf();
    }

    /**
    * Accessor to set the CRLF style
    */
    public function setCrlf($crlf = "\n")
    {
        if (! defined('CRLF')) {
            define('CRLF', $crlf);
        }

        if (! defined('MAIL_MIMEPART_CRLF')) {
            define('MAIL_MIMEPART_CRLF', $crlf);
        }
    }

    /**
     * Gnupg version check; sets internal variable once
     *
     * @access protected
     * @return void
     */
    protected function gpgCheckVersion()
    {

        //////////////////////////////////////////
        // find which version of GnuPG we're using
        //////////////////////////////////////////

        ///////////////////////////////
        // open the GnuPG process and get the reply
        // we're only concerned with the first line of output, so use "false" as last argument
        $commandline = $this->_gpg_path
                    . ' --version';
        $ret = $this->gpgExecProc($commandline, null, false);

        /////////////////////////////////////////////////////
        // get the version (we are only concerned with the first line of output,
        // which was read from gpg-process-output as single-line-read into $ret[1]
        $gpg_version_output = $ret[0];

        ///////////////////////////////////////////////
        // sanity check - see if we're working with gpg
        if (preg_match('/^gpg /', $gpg_version_output) == 0) {
            $error_msg = 'gpg executable is not GnuPG: "' . $this->_gpg_path . '"';
            trigger_error($error_msg, E_USER_ERROR);
            // if an error message directs you to the line above please
            // double check that your path to gpg is really GnuPG
            die();
        }

        /////////////////////////////////////////////////////////////
        // pick the version number out of $gpg_encrypt_version_output
        // we'll need this so we can determine the correct
        // way to tell GnuPG how to "always trust"
        $gpg_gpg_version = preg_replace('/^.* /', '', $gpg_version_output);

        ////////////////////////////////////////////////////////
        // depending on which version of GnuPG we're using there
        // are two different ways to specify "always trust"
        if ("$gpg_gpg_version" < '1.2.3') {
            $this->_gpg_trust = '--always-trust';       // the old way
        } else {
            $this->_gpg_trust = '--trust-model always'; // the new way
        }

        /////////////////////////////////////////////
        // unset variables that we don't need anymore
        unset(
            $gpg_version_output,
            $gpg_gpg_version,
            $commandline
        );

        ////////////////////////////////////////
        // we're done checking the GnuPG version
        ////////////////////////////////////////
        return;
    }

    /**
     * Gnupg process call function
     *
     * @param string    $gpg_proc_call
     * @param string    $gpg_proc_input
     * @param boolean   $read_multilines
     * @access protected
     * @return array
     *      0 => process call output (STDOUT)
     *      1 => warnings and notices (STDERR)
     *      2 => exit status
     */
    protected function gpgExecProc($gpg_proc_call = '', $gpg_proc_input = null, $read_multilines = true)
    {

        if ($gpg_proc_call == '') {
            die;
        }

        //////////////////////////////////////////////
        // set up pipes for handling I/O to/from GnuPG
        $gpg_descriptorspec = [
            0 => ["pipe", "r"],  // STDIN is a pipe that GnuPG will read from
            1 => ["pipe", "w"],  // STDOUT is a pipe that GnuPG will write to
            2 => ["pipe", "w"]   // STDERR is a pipe that GnuPG will write to
        ];

        ///////////////////////////////
        // this opens the GnuPG process
        $gpg_process = proc_open(
            $gpg_proc_call,
            $gpg_descriptorspec,
            $gpg_pipes
        );

        //////////////////////////////////////////////////////////////////
        // this writes the "$gpg_encrypt_secret_message" to GnuPG on STDIN
        if (is_resource($gpg_process)) {
            if ($gpg_proc_input != null) {
                fwrite($gpg_pipes[0], $gpg_proc_input);
            }
            fclose($gpg_pipes[0]);

            /////////////////////////////////////////////////////////
            // this reads the output from GnuPG from STDOUT
            $gpg_proc_output = '';
            if ($read_multilines) {
                while (! feof($gpg_pipes[1])) {
                    $gpg_proc_output .= fgets($gpg_pipes[1], 1024);
                }
                fclose($gpg_pipes[1]);
            } else {
                $gpg_proc_output = fgets($gpg_pipes[1], 1024);
            }

            /////////////////////////////////////////////////////////
            // this reads warnings and notices from GnuPG from STDERR
            $gpg_error_message = '';
            while (! feof($gpg_pipes[2])) {
                $gpg_error_message .= fgets($gpg_pipes[2], 1024);
            }
            fclose($gpg_pipes[2]);

            /////////////////////////////////////////
            // this collects the exit status of GnuPG
            $gpg_exit_status = proc_close($gpg_process);

            ////////////////////////////////////////////
            // unset variables that are no longer needed
            // and can only cause trouble
            unset(
                $gpg_descriptorspec,
                $gpg_process,
                $gpg_pipes
            );

            ////////////////////////////////////
            // this returns an array containing:
            // [0] encrypted output (STDOUT)
            // [1] warnings and notices (STDERR)
            // [2] exit status
            return [$gpg_proc_output, $gpg_error_message,  $gpg_exit_status];
        } else {
            ////////////////////////////////////////////
            // unset variables that are no longer needed
            // and can only cause trouble
            unset(
                $gpg_descriptorspec,
                $gpg_process,
                $gpg_pipes
            );

            //////////////////////////////
            // set output as otherwise nothing
            $gpg_proc_output = '';
            $gpg_error_message = 'Fatal process call error: Process call failed!';
            $gpg_exit_status = 99;
            return [$gpg_proc_output, $gpg_error_message,  $gpg_exit_status];
        }
    }


    /////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //
    // Encryption function; encrypts & signs the message
    //
    // usage:
    // array gpg_encrypt(secret-message, recipients);
    //
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Encryption function; encrypts & signs the message
     *
     * @param string    $secret-message
     * @param array/string  $recipients
     * @access public
     * @return array
     *      0 => encrypted message output (STDOUT)
     *      1 => warnings and notices (STDERR)
     *      2 => exit status
     */
    public function gpg_encrypt()
    {

        global $prefs;

        //////////////////////////////////////////////////////////
        // sanity check - make sure there are at least 2 arguments
        // any extra arguments are considered to be additional key IDs
        if (func_num_args() < 2) {
            trigger_error("gpg_encrypt() requires at least 2 arguments", E_USER_ERROR);
            // if an error message directs you to the line above please
            // double check that you are providing at least 2 arguments
            die();
        }

        ////////////////////////////////
        // assign arguments to variables
        $gpg_args = func_get_args();
        $gpg_secret_message = array_shift($gpg_args);   // 1st argument - secret message; let the gpg process to deal with empty/faulty content

        ///////////////////////////////////////////////////////////////////////
        // make sure that each recipient has the message encrypted to their key
        // the 2nd argument, and any subsequent arguments, are key IDs
        $gpg_recipient_list = '';
        foreach ($gpg_args as $gpg_recipient) {
            if (is_array($gpg_recipient)) {
                foreach ($gpg_recipient as &$item) {
                    $gpg_recipient_list .= ' -r ' . $item;
                }
            } else {
                $gpg_recipient_list .= " -r ${gpg_recipient}";
            }
        }

        //////////////////////////////////////////
        // find which version of GnuPG we're using
        //////////////////////////////////////////
        if ($this->_gpg_trust == '') {
            $this->gpgCheckVersion();
        }

        ///////////////////////////////
        // open the GnuPG process and get the reply
        $commandline = '';
        if ($prefs['openpgp_gpg_signer_passphrase_store'] == 'file') {
            // get signer-key passphrase from a file
            $commandline .= $this->_gpg_path
                    . ' --no-random-seed-file'
                    . ' --homedir ' . $this->_gpg_home
                    . ' ' . $this->_gpg_trust
                    . ' --batch'
                    . ' --local-user ' . $this->_gpg_sgn_id
                    . ' --passphrase-file ' . $this->_gpg_sgn_passfile_path
                    . ' -sea ' . $gpg_recipient_list
                    . ' ';
        } else {
            // get signer-key passphrase from preferences
            $commandline .= $this->_gpg_path
                    . ' --no-random-seed-file'
                    . ' --homedir ' . $this->_gpg_home
                    . ' ' . $this->_gpg_trust
                    . ' --batch'
                    . ' --local-user ' . $this->_gpg_sgn_id
                    . ' --passphrase ' . $this->_gpg_sgn_passphrase
                    . ' -sea ' . $gpg_recipient_list
                    . ' ';
        }
        $ret = $this->gpgExecProc($commandline, $gpg_secret_message);

        unset(
            $gpg_args,
            $gpg_secret_message,
            $gpg_recipient_list,
            $commandline
        );

        ////////////////////////////////////
        // this returns an array containing:
        // [0] encrypted output (STDOUT)
        // [1] warnings and notices (STDERR)
        // [2] exit status
        return $ret;
    }

    /////////////////////////////////////////////////////////////
    //
    // Get the public-key fingerprint for a key associated with the ID
    //
    /////////////////////////////////////////////////////////////

    /**
     * Get public-key fingerprint
     *
     * @param string    $gpg_key_id
     * @access public
     * @return array
     *      0 => public key gingerprint output (STDOUT)
     *      1 => warnings and notices (STDERR)
     *      2 => exit status
     */
    public function gpg_getFingerprint($gpg_key_id = null)
    {

        //////////////////////////////////////////////////////////
        // sanity check - make sure there is 1 argument
        if ($gpg_key_id == null) {
            trigger_error("gpg_getFingerprint() requires 1 argument", E_USER_ERROR);
            // if an error message directs you to the line above please
            // double check that you are providing 1 argument
            die();
        }

        ///////////////////////////////////////////////////////////////////////
        // the argument is key ID; if array, accept only the first
        $gpg_key_id_to_return = '';
        if (is_array($gpg_key_id)) {
            foreach ($gpg_key_id as &$item) {
                $gpg_key_id_to_return .= $item;
                break;
            }
        } else {
            $gpg_key_id_to_return .= $gpg_key_id;
        }

        //////////////////////////////////////////
        // find which version of GnuPG we're using
        //////////////////////////////////////////
        if ($this->_gpg_trust == '') {
            $this->gpgCheckVersion();
        }

        ///////////////////////////////
        // open the GnuPG process and get the reply
        $commandline = $this->_gpg_path
                    . ' --homedir ' . $this->_gpg_home
                    . ' ' . $this->_gpg_trust
                    . ' --fingerprint'
                    . ' --list-sigs ' . $gpg_key_id_to_return
                    . ' ';
        $ret = $this->gpgExecProc($commandline);

        unset(
            $gpg_key_id_to_return,
            $commandline
        );

        ////////////////////////////////////
        // this returns an array containing:
        // [0] fingerprint output (STDOUT)
        // [1] warnings and notices (STDERR)
        // [2] exit status
        return $ret;
    }

    //////////////////////////////////////////////////////////////////////////////
    //
    // Get the public-key ascii-armor-block for a key associated with the ID
    //
    //////////////////////////////////////////////////////////////////////////////

    /**
     * Get public-key ascii armor block
     *
     * @param string    $gpg_key_id
     * @access public
     * @return array
     *      0 => public key armor output (STDOUT)
     *      1 => warnings and notices (STDERR)
     *      2 => exit status
     */
    public function gpg_getPublicKey($gpg_key_id = null)
    {

        //////////////////////////////////////////////////////////
        // sanity check - make sure there is 1 argument
        if ($gpg_key_id == null) {
            trigger_error("gpg_getPublicKey() requires 1 argument", E_USER_ERROR);
            // if an error message directs you to the line above please
            // double check that you are providing 1 argument
            die();
        }

        ///////////////////////////////////////////////////////////////////////
        // the argument is key ID; if array, accept only the first
        $gpg_key_id_to_return = '';
        if (is_array($gpg_key_id)) {
            foreach ($gpg_key_id as &$item) {
                $gpg_key_id_to_return .= $item;
                break;
            }
        } else {
            $gpg_key_id_to_return .= $gpg_key_id;
        }

        //////////////////////////////////////////
        // find which version of GnuPG we're using
        //////////////////////////////////////////
        if ($this->_gpg_trust == '') {
            $this->gpgCheckVersion();
        }

        ///////////////////////////////
        // open the GnuPG process and get the reply
        $commandline = $this->_gpg_path
                    . ' --homedir ' . $this->_gpg_home
                    . ' ' . $this->_gpg_trust
                    . ' --export --armor ' . $gpg_key_id_to_return
                    . ' ';
        $ret = $this->gpgExecProc($commandline);

        unset(
            $gpg_key_id_to_return,
            $commandline
        );

        ////////////////////////////////////
        // this returns an array containing:
        // [0] public key armor output (STDOUT)
        // [1] warnings and notices (STDERR)
        // [2] exit status
        return $ret;
    }

    /////////////////////////////////////////////////////