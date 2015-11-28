<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    auth
 * @subpackage mcae
 * @copyright  2011 Andrew "Kama" (kamasutra12@yandex.ru)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');
require_once($CFG->dirroot.'/cohort/lib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
require_once($CFG->dirroot.'/auth/mcae/lib.php');

class auth_plugin_mcae extends auth_plugin_manual {

    const COMPONENT_NAME = 'auth_mcae';

    /**
     * Constructor.
     */
    function auth_plugin_mcae() {
		global $CFG;
		require_once($CFG->dirroot . '/lib/mustache/src/Mustache/Autoloader.php');
		
        $this->authtype = 'mcae';
        $this->config = get_config(self::COMPONENT_NAME);
		Mustache_Autoloader::register();

		$this->mustache = new Mustache_Engine;
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
        set_config('mainrule_fld',   $config->mainrule_fld,   self::COMPONENT_NAME);
        set_config('secondrule_fld', $config->secondrule_fld, self::COMPONENT_NAME);
        set_config('replace_arr',    $config->replace_arr,    self::COMPONENT_NAME);
        set_config('delim',          $config->delim,          self::COMPONENT_NAME);
        set_config('donttouchusers', $config->donttouchusers, self::COMPONENT_NAME);
        set_config('enableunenrol',  $config->enableunenrol,  self::COMPONENT_NAME);

        return true;
    }

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

        $context = context_system::instance();
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
            $clause['component'] = self::COMPONENT_NAME;
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
		profile_load_custom_fields($user);
        $user_profile_data = mcae_prepare_profile_data($user, $this->config->secondrule_fld);

        // Additional values for email
        list($email_username,$email_domain) = explode("@", $user_profile_data['email']);

        // email root domain
        $email_domain_array = explode('.',$email_domain);
        if(count($email_domain_array) > 2) {
            $email_rootdomain = $email_domain_array[count($email_domain_array)-2].'.'.$email_domain_array[count($email_domain_array)-1];
        } else {
            $email_rootdomain = $email_domain;
        }
        $user_profile_data['email'] = array('full' => $user_profile_data['email'], 'username' => $email_username, 'domain' => $email_domain, 'rootdomain' => $email_rootdomain);

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

        // Find %split function
        foreach ($templates_tpl as $item) {
            if (preg_match('/(?<full>%split\((?<fld>\w*)\|(?<delim>.{1,5})\))/', $item, $split_params)) {
                // Split!
                $splitted = explode($split_params['delim'], $user_profile_data[$split_params['fld']]);
                foreach($splitted as $key => $val) {
                    $user_profile_data[$split_params['fld']."_$key"] = $val;
                    $templates[] = strtr($item, array("${split_params['full']}" => "{{ ${split_params['fld']}_$key }}"));
                }
            } else {
                $templates[] = $item;
            }
        }

        $processed = array();

        // Process templates with Mustache
        

        foreach ($templates as $cohort) {
            $cohortname = $this->mustache->render($cohort, $user_profile_data);
            $cohortname = (!empty($replacements)) ? strtr($cohortname, $replacements) : $cohortname;

            if ($cohortname == '') {
                continue; // We don't want an empty cohort name
            };

            $cid = array_search($cohortname, $cohorts_list);
            if ($cid !== false) {

                if (!$DB->record_exists('cohort_members', array('cohortid'=>$cid, 'userid'=>$user->id))) {
                    cohort_add_member($cid, $user->id);
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
				
				// Prevent creation new cohorts with same names
				$cohorts_list[$cid] = $cohortname;
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
                };
            };
        };

    }

}
