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

// $hassiteconfig or $ADMIN->fulltree / What's the difference?

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
    foreach ($USER as $key => $val){
        if (is_array($val)) {
            if (isset($val['text'])) {
                $fldlist[] = "<span title=\"%$key\">%$key</span>";
            };
        } else {
            $fldlist[] = "<span title=\"%$key\">%$key</span>";
        };
    }

    $fldlist[] = "<span title=\"%email_username\">%email_username</span>";
    $fldlist[] = "<span title=\"%email_domain\">%email_domain</span>";

    sort($fldlist);

    $settings->add(new admin_setting_heading('auth_mcae_profile_help', get_string('auth_profile_help', 'auth_mcae'), implode(', ', $fldlist)));

    $settings->add(new admin_setting_configselect('auth_mcae/delim', get_string('auth_delim', 'auth_mcae'), get_string('auth_delim_help', 'auth_mcae'), 'CR+LF', array('CR+LF'=>'CR+LF', 'CR'=>'CR', 'LF'=>'LF')));
    $settings->add(new admin_setting_configtext('auth_mcae/secondrule_fld', get_string('auth_secondrule_fld', 'auth_mcae'),'', 'n/a'));
    $settings->add(new admin_setting_configtextarea('auth_mcae/replace_arr', get_string('auth_replace_arr', 'auth_mcae'), '', ''));
    $settings->add(new admin_setting_configtextarea('auth_mcae/donttouchusers', get_string('auth_donttouchusers', 'auth_mcae'), '', ''));
    $settings->add(new admin_setting_configcheckbox('auth_mcae/enableunenrol', get_string('auth_enableunenrol', 'auth_mcae'), '', 0));
}
