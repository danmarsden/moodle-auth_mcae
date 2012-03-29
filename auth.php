<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');
require_once($CFG->dirroot.'/cohort/lib.php');


/**
 * This is a copy of Manual authentication plugin.
 * 2 function changed: user_update and process_config
 *
 * @package    auth
 * @subpackage mcae
 * @copyright  2011 Andrew "Kama" (kamasutra12@yandex.ru)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_plugin_mcae extends auth_plugin_base {

    /**
     * Constructor.
     */
    function auth_plugin_mcae() {
        $this->authtype = 'mcae';
        $this->config = get_config('auth_mcae');
    }

    /**
     * Returns true if the username and password work and false if they are
     * wrong or don't exist. (Non-mnet accounts only!)
     *
     * @param string $username The username
     * @param string $password The password
     * @return bool Authentication success or failure.
     */
    function user_login($username, $password) {
        global $CFG, $DB, $USER;
        if (!$user = $DB->get_record('user', array('username'=>$username, 'mnethostid'=>$CFG->mnet_localhost_id))) {
            return false;
        }
        if (!validate_internal_user_password($user, $password)) {
            return false;
        }
        if ($password === 'changeme') {
            // force the change - this is deprecated and it makes sense only for manual auth,
            // because most other plugins can not change password easily or
            // passwords are always specified by users
            set_user_preference('auth_forcepasswordchange', true, $user->id);
        }
        return true;
    }

    /**
     * Updates the user's password.
     *
     * Called when the user password is updated.
     *
     * @param  object  $user        User table object
     * @param  string  $newpassword Plaintext password
     * @return boolean result
     */
    function user_update_password($user, $newpassword) {
        $user = get_complete_user_data('id', $user->id);
        return update_internal_user_password($user, $newpassword);
    }

    function prevent_local_passwords() {
        return false;
    }

    /**
     * Returns true if this authentication plugin is 'internal'.
     *
     * @return bool
     */
    function is_internal() {
        return true;
    }

    /**
     * Returns true if this authentication plugin can change the user's
     * password.
     *
     * @return bool
     */
    function can_change_password() {
        return true;
    }

    /**
     * Returns the URL for changing the user's pw, or empty if the default can
     * be used.
     *
     * @return moodle_url
     */
    function change_password_url() {
        return null;
    }

    /**
     * Returns true if plugin allows resetting of internal password.
     *
     * @return bool
     */
    function can_reset_password() {
        return true;
    }

   /**
    * Confirm the new user as registered. This should normally not be used,
    * but it may be necessary if the user auth_method is changed to manual
    * before the user is confirmed.
    *
    * @param string $username
    * @param string $confirmsecret
    */
    function user_confirm($username, $confirmsecret = null) {
        global $DB;

        $user = get_complete_user_data('username', $username);

        if (!empty($user)) {
            if ($user->confirmed) {
                return AUTH_CONFIRM_ALREADY;
            } else {
                $DB->set_field("user", "confirmed", 1, array("id"=>$user->id));
                $DB->set_field("user", "firstaccess", time(), array("id"=>$user->id));
                return AUTH_CONFIRM_OK;
            }
        } else  {
            return AUTH_CONFIRM_ERROR;
        }
    }


    /**
     * Processes and stores configuration data for this authentication plugin.
     * $this->config->somefield
     */
    function process_config($config) {
        // set to defaults if undefined

	if (!isset($config->mainrule_fld)) {
	    $config->mainrule_fld = '';
	}
	if (!isset($config->secondrule_fld)) {
	    $config->secondrule_fld = 'n/a';
	}
	if (!isset($config->replace_arr)) {
	    $config->replace_arr = '';
	}
	if (!isset($config->delim)) {
	    $config->delim = 'CR+LF';
	}
	if (!isset($config->donttouchusers)) {
	    $config->donttouchusers = '';
	}
	if (!isset($config->enableunenrol)) {
	    $config->enableunenrol = 0;
	}
        // save settings
        set_config('mainrule_fld', $config->mainrule_fld, 'auth_mcae');
        set_config('secondrule_fld', $config->secondrule_fld, 'auth_mcae');
        set_config('replace_arr', $config->replace_arr, 'auth_mcae');
        set_config('delim', $config->delim, 'auth_mcae');
        set_config('donttouchusers', $config->donttouchusers, 'auth_mcae');
        set_config('enableunenrol', $config->enableunenrol, 'auth_mcae');

        return true;
    }

    /**
     * Called when the user record is updated.
     * Modifies user in external database. It takes olduser (before changes) and newuser (after changes)
     * compares information saved modified information to external db.
     *
     * @param mixed $olduser     Userobject before modifications    (without system magic quotes)
     * @param mixed $newuser     Userobject new modified userobject (without system magic quotes)
     * @return boolean true if updated or update ignored; false if error
     *
     */
//    function user_update($olduser, $newuser) {
//        return true;
//  }
    /**
     * Post authentication hook.
     * This method is called from authenticate_user_login() for all enabled auth plugins.
     *
     * @param object $user user object, later used for $USER
     * @param string $username (with system magic quotes)
     * @param string $password plain text password (with system magic quotes)
     */
    function user_authenticated_hook(&$user, $username, $password) {
	global $DB;

        $context = get_context_instance(CONTEXT_SYSTEM);
        $uid = $user->id;
        // Ignore users from don't_touch list
        $ignore = explode(",",$this->config->donttouchusers);

        if (!empty($ignore) AND array_search($username, $ignore) !== false) {
            return true;
        };

        // Ignore guests
        if ($uid < 2) {
            return true;
        };
        
// ********************** Get COHORTS data
        $clause = array('contextid'=>$context->id);
        if ($this->config->enableunenrol == 1) {
            $clause['component'] = 'auth_mcae';
        };

        $cohorts = $DB->get_records('cohort', $clause);

        $cohorts_list = array();
        foreach($cohorts as $cohort) {
            $cid = $cohort->id;
	    $cname = format_string($cohort->name);
            $cohorts_list[$cid] = $cname;
        }
    
// ********************** Get advanced user data
//    $customfld = profile_user_record($uid);

        profile_load_data($user);
        $cust_arr = array();
        foreach ($user as $key => $val){
// "Text input" profile field is an array with 'text' ... aaahh ... val,expr,...
            if (is_array($val)) {
                $text = (isset($val['text'])) ? $val['text'] : '';
            } else {
                $text = $val;
            };
            $cust_arr['%'.$key] = ($text == '') ? format_string($this->config->secondrule_fld) : format_string($text);
        }; 

        list($email_username,$email_domain) = explode("@", $cust_arr['%email']);
        $cust_arr['%email_username'] = $email_username;
        $cust_arr['%email_domain'] = $email_domain;

        $delimiter = $this->config->delim;
        $delim = strtr($delimiter, array('CR+LF' => chr(13).chr(10), 'CR' => chr(13), 'LF' => chr(10)));
// ********************** Calculate a cohort names for user
        $repl_arr_tpl = $this->config->replace_arr;

        $repl_arr = array();
        if (!empty($repl_arr_tpl)) {
            $repl_arr_pre = explode($delim, $repl_arr_tpl);
            foreach ($repl_arr_pre as $rap) {
                list($key, $val) = explode("|", $rap);
                $repl_arr[$key] = $val;
            };
        };

// ********************** Generate cohorts array
        $cohorts_arr_tpl = $this->config->mainrule_fld;

        $cohorts_arr = array();
        if (!empty($cohorts_arr_tpl)) {
            $cohorts_arr = explode($delim, $cohorts_arr_tpl);
        } else {
            return; //Empty mainrule
        };
        
        $processed = array();

        foreach ($cohorts_arr as $cohort) {
            $cohortname = strtr($cohort, $cust_arr);
            $cohortname = (!empty($repl_arr)) ? strtr($cohortname, $repl_arr) : $cohortname;

            if ($cohortname == '') {
                continue; // We don't want an empty cohort name
            };

            $cid = array_search($cohortname, $cohorts_list);
            if ($cid !== false) {

                if (!$DB->record_exists('cohort_members', array('cohortid'=>$cid, 'userid'=>$user->id))) {
                    cohort_add_member($cid, $user->id);
                    add_to_log(SITEID, 'user', 'Added to cohort ID ' . $cid, "view.php?id=$user->id&course=".SITEID, $user->id, 0, $user->id);
                } else {
                    add_to_log(SITEID, 'user', 'Already exists in cohort ID ' . $cid, "view.php?id=$user->id&course=".SITEID, $user->id, 0, $user->id);
                };
            } else {
// Cohort not exist so create a new one
                add_to_log(SITEID, 'user', 'Cohort not exist ID so screate a new one' , "view.php?id=$user->id&course=".SITEID, $user->id, 0, $user->id);
                $newcohort = new stdClass();
                $newcohort->name = $cohortname;
//        $newcohort->idnumber = "auto_" . substr($cohortname, 0, 20) . "_" . date("d-m-Y");
                $newcohort->description = "created ". date("d-m-Y");
                $newcohort->contextid = $context->id;
                if ($this->config->enableunenrol == 1) {
                    $newcohort->component = "auth_mcae";
                };
                $cid = cohort_add_cohort($newcohort);
                cohort_add_member($cid, $user->id);

                add_to_log(SITEID, 'user', 'Added to cohort ID ' . $cid, "view.php?id=$user->id&course=".SITEID, $user->id, 0, $user->id);
            };
            $processed[] = $cid;
        };
        //Unenrol user
        if ($this->config->enableunenrol == 1) {
        //List of cohorts where this user enrolled
            $sql = "SELECT c.id AS cid FROM {cohort} c JOIN {cohort_members} cm ON cm.cohortid = c.id WHERE c.component = 'auth_mcae' AND cm.userid = $uid";
            $enrolledcohorts = $DB->get_records_sql($sql);

//print_r($processed);
//print_r($enrolledcohorts);
//die();

            foreach ($enrolledcohorts as $ec) {
                if(array_search($ec->cid, $processed) === false) {
                    cohort_remove_member($ec->cid, $uid);
                    add_to_log(SITEID, 'user', 'Removed from cohort ID ' . $ec->cid, "view.php?id=$user->id&course=".SITEID, $user->id, 0, $user->id);
                };
            };
        };

    }

}


