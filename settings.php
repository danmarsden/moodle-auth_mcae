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

defined('MOODLE_INTERNAL') || die;

global $USER;

require_once($CFG->dirroot.'/user/profile/lib.php');
require_once($CFG->dirroot.'/auth/mcae/lib.php');

if ($hassiteconfig) { // needs this condition or there is error on login page
    $ADMIN->add('accounts', new admin_externalpage('cohorttoolmcae',
            get_string('auth_cohorttoolmcae', 'auth_mcae'),
            new moodle_url('/auth/mcae/convert.php')));

    $ADMIN->add('accounts', new admin_externalpage('cohortviewmcae',
            get_string('auth_cohortviewmcae', 'auth_mcae'),
            new moodle_url('/auth/mcae/view.php')));

}

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtextarea('auth_mcae/mainrule_fld', get_string('auth_mainrule_fld', 'auth_mcae'), '', ''));

    // Profile field helper
    $fldlist = array();
    $usr_helper = $DB->get_record('user', array('id' => 2));

    profile_load_data($usr_helper);
	profile_load_custom_fields($usr_helper);
    	
    $fldlist = mcae_prepare_profile_data($usr_helper);
	
    // Additional values for email
    list($email_username,$email_domain) = explode("@", $fldlist['email']);

    // Email root domain
    $email_domain_array = explode('.',$email_domain);
    if(count($email_domain_array) > 2) {
        $email_rootdomain = $email_domain_array[count($email_domain_array)-2].'.'.$email_domain_array[count($email_domain_array)-1];
    } else {
        $email_rootdomain = $email_domain;
    }
    $fldlist['email'] = array('full' => $fldlist['email'], 'username' => $email_username, 'domain' => $email_domain, 'rootdomain' => $email_rootdomain);
	
    //print_r($fldlist);
    $help_array = array();
    mcae_print_profile_data($fldlist, '', $help_array);

    //print_r($usr_helper);
    //print_r($help_array);
	
    $help_text = implode(', ', $help_array);

    $settings->add(new admin_setting_heading('auth_mcae_profile_help', get_string('auth_profile_help', 'auth_mcae'), $help_text));

    $settings->add(new admin_setting_configselect('auth_mcae/delim', get_string('auth_delim', 'auth_mcae'), get_string('auth_delim_help', 'auth_mcae'), 'CR+LF', array('CR+LF'=>'CR+LF', 'CR'=>'CR', 'LF'=>'LF')));
    $settings->add(new admin_setting_configtext('auth_mcae/secondrule_fld', get_string('auth_secondrule_fld', 'auth_mcae'),'', 'n/a'));
    $settings->add(new admin_setting_configtextarea('auth_mcae/replace_arr', get_string('auth_replace_arr', 'auth_mcae'), '', ''));
    $settings->add(new admin_setting_configtextarea('auth_mcae/donttouchusers', get_string('auth_donttouchusers', 'auth_mcae'), '', ''));
    $settings->add(new admin_setting_configcheckbox('auth_mcae/enableunenrol', get_string('auth_enableunenrol', 'auth_mcae'), '', 0));
}
