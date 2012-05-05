<?php

/**
 * Adds this plugin to the admin menu.
 *
 * @package    auth
 * @subpackage mcae
 * @copyright  2011 Andrew "Kama" (kamasutra12@yandex.ru)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

global $USER;

require_once($CFG->dirroot.'/user/profile/lib.php');

if ($hassiteconfig) { // needs this condition or there is error on login page
    $ADMIN->add('root', new admin_externalpage('cohorttoolmcae',
            get_string('auth_cohorttoolmcae', 'auth_mcae'),
            new moodle_url('/auth/mcae/convert.php')));

    $ADMIN->add('root', new admin_externalpage('cohortviewmcae',
            get_string('auth_cohortviewmcae', 'auth_mcae'),
            new moodle_url('/auth/mcae/view.php')));

}

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtextarea('auth_mcae/mainrule_fld', get_string('auth_mainrule_fld', 'auth_mcae'), '', ''));

// Profile field helper
    $fldlist = array();
    $usr_helper = $USER;

    profile_load_data($usr_helper);
    foreach ($usr_helper as $key => $val){
        $fld = preg_replace('/profile_field_/', 'profile_field_raw_', $key);
        if (is_array($val)) {
            if (isset($val['text'])) {
                $fldlist[] = "<span title=\"%$fld\">%$fld</span>";
            };
        } else {
            $fldlist[] = "<span title=\"%$fld\">%$fld</span>";
        };
    }; 

    // Custom profile field values
    foreach ($usr_helper->profile as $key => $val) {
        $fldlist[] = "<span title=\"%profile_field_$key\">%profile_field_$key</span>";
    };

    // Additional values for email
    $fldlist[] = "<span title=\"%email_username\">%email_username</span>";
    $fldlist[] = "<span title=\"%email_domain\">%email_domain</span>";

    sort($fldlist);
    $help_text = implode(', ', $fldlist);

    $settings->add(new admin_setting_heading('auth_mcae_profile_help', get_string('auth_profile_help', 'auth_mcae'), $help_text));

    $settings->add(new admin_setting_configselect('auth_mcae/delim', get_string('auth_delim', 'auth_mcae'), get_string('auth_delim_help', 'auth_mcae'), 'CR+LF', array('CR+LF'=>'CR+LF', 'CR'=>'CR', 'LF'=>'LF')));
    $settings->add(new admin_setting_configtext('auth_mcae/secondrule_fld', get_string('auth_secondrule_fld', 'auth_mcae'),'', 'n/a'));
    $settings->add(new admin_setting_configtextarea('auth_mcae/replace_arr', get_string('auth_replace_arr', 'auth_mcae'), '', ''));
    $settings->add(new admin_setting_configtextarea('auth_mcae/donttouchusers', get_string('auth_donttouchusers', 'auth_mcae'), '', ''));
    $settings->add(new admin_setting_configcheckbox('auth_mcae/enableunenrol', get_string('auth_enableunenrol', 'auth_mcae'), '', 0));
}
