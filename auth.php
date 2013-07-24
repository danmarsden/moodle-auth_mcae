<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');
require_once($CFG->dirroot.'/cohort/lib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');

/**
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
	global $DB, $SESSION;

        $context = get_context_instance(CONTEXT_SYSTEM);
        $uid = $user->id;
        // Ignore users from don't_touch list
        $ignore = explode(",",$this->config->donttouchusers);

        if (!empty($ignore) AND array_search($username, $ignore) !== false) {
            $SESSION->mcautoenrolled = TRUE;
            return true;
        };

        // Ignore guests
        if ($uid < 2) {
            $SESSION->mcautoenrolled = TRUE;
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

        // Get advanced user data
        profile_load_data($user);
        $user_profile_data = array();
        foreach ($user as $key => $val){
            if (is_array($val)) {
                $text = (isset($val['text'])) ? $val['text'] : '';
            } else {
                $text = $val;
            };

            // Raw custom profile fields
            $fld_key = preg_replace('/profile_field_/', 'profile_field_raw_', $key);
            $user_profile_data["%$fld_key"] = ($text == '') ? format_string($this->config->secondrule_fld) : format_string($text);
        };

        // Custom profile field values
        foreach ($user->profile as $key => $val) {
            $user_profile_data["%profile_field_$key"] = ($val == '') ? format_string($this->config->secondrule_fld) : format_string($val);
        };

        // Additional values for email
        list($email_username,$email_domain) = explode("@", $user_profile_data['%email']);
        $user_profile_data['%email_username'] = $email_username;
        $user_profile_data['%email_domain'] = $email_domain;

        // Delimiter
        $delimiter = $this->config->delim;
        $delim = strtr($delimiter, array('CR+LF' => chr(13).chr(10), 'CR' => chr(13), 'LF' => chr(10)));

        // Calculate a cohort names for user
        $replacements_tpl = $this->config->replace_arr;

        $replacements = array();
        if (!empty($replacements_tpl)) {
            $replacements_pre = explode($delim, $replacements_tpl);
            foreach ($replacements_pre as $rap) {
                list($key, $val) = explode("|", $rap);
                $replacements[$key] = $val;
            };
        };

        // Generate cohorts array
        $main_rule = $this->config->mainrule_fld;

        $templates_tpl = array();
        $templates = array();
        if (!empty($main_rule)) {
            $templates_tpl = explode($delim, $main_rule);
        } else {
            $SESSION->mcautoenrolled = TRUE;
            return; //Empty mainrule
        };


        // Split!
        foreach ($templates_tpl as $item) {
            if (preg_match('/(?<full>%split\((?<fld>%\w*)\|(?<delim>.{1,5})\))/', $item, $split_params)) {
                $splitted = explode($split_params['delim'], $user_profile_data[$split_params['fld']]);
                foreach($splitted as $key => $val) {
                    $user_profile_data[$split_params['fld']."_$key"] = $val;
                    $templates[] = strtr($item, array("${split_params['full']}" => "${split_params['fld']}_$key"));
                }
            } else {
                $templates[] = $item;
            }
        }

        $processed = array();
        $log_new = array();
        $log_unenrolled = array();
        $log_exist = array();
        $log_add = array();

        foreach ($templates as $cohort) {
            $cohortname = strtr($cohort, $user_profile_data);
            $cohortname = (!empty($replacements)) ? strtr($cohortname, $replacements) : $cohortname;

            if ($cohortname == '') {
                continue; // We don't want an empty cohort name
            };

            $cid = array_search($cohortname, $cohorts_list);
            if ($cid !== false) {

                if (!$DB->record_exists('cohort_members', array('cohortid'=>$cid, 'userid'=>$user->id))) {
                    cohort_add_member($cid, $user->id);
                    $log_add[] = $cid;
                } else {
                    $log_exist[] = $cid;
                };
            } else {
                // Cohort not exist so create a new one
                $newcohort = new stdClass();
                $newcohort->name = $cohortname;
                $newcohort->description = "created ". date("d-m-Y");
                $newcohort->contextid = $context->id;
                if ($this->config->enableunenrol == 1) {
                    $newcohort->component = "auth_mcae";
                };
                $cid = cohort_add_cohort($newcohort);
                cohort_add_member($cid, $user->id);
                $log_new[] = $cid;
            };
            $processed[] = $cid;
        };
        $SESSION->mcautoenrolled = TRUE;

        //Unenrol user
        if ($this->config->enableunenrol == 1) {
        //List of cohorts where this user enrolled
            $sql = "SELECT c.id AS cid FROM {cohort} c JOIN {cohort_members} cm ON cm.cohortid = c.id WHERE c.component = 'auth_mcae' AND cm.userid = $uid";
            $enrolledcohorts = $DB->get_records_sql($sql);

            foreach ($enrolledcohorts as $ec) {
                if(array_search($ec->cid, $processed) === false) {
                    cohort_remove_member($ec->cid, $uid);
                    $log_unenrolled[] = $ec->cid;
                };
            };
        };
        // LOG
        if ($log_exist) {
          add_to_log(SITEID, 'user', 'already exist in cohorts', "view.php?id=$user->id&course=".SITEID, implode(', ', $log_exist), 0, $user->id);
        }
        if ($log_add) {
          add_to_log(SITEID, 'user', 'added to cohorts', "view.php?id=$user->id&course=".SITEID, implode(', ', $log_add), 0, $user->id);
        }
        if ($log_new) {
          add_to_log(SITEID, 'user', 'areated cohorts', "view.php?id=$user->id&course=".SITEID, implode(', ', $log_new), 0, $user->id);
        }
        if ($log_unenrolled) {
          add_to_log(SITEID, 'user', 'removed from cohorts', "view.php?id=$user->id&course=".SITEID, implode(', ', $log_unenrolled), 0, $user->id);
        }

    }
}
