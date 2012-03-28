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

if ($hassiteconfig) { // needs this condition or there is error on login page
    $ADMIN->add('root', new admin_externalpage('cohorttoolmcae',
            get_string('cohorttoolmcae', 'auth_mcae'),
            new moodle_url('/auth/mcae/convert.php')));
}
