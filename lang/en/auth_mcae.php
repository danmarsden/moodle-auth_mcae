<?php

/**
 * Autoenrol cohort authentication plugin version information
 *
 * @package    auth
 * @subpackage mcae
 * @copyright  2011 Andrew "Kama" (kamasutra12@yandex.ru) 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['auth_mcaedescription'] = 'This method provides the way to automathic enrol user into cohort.';
$string['pluginname'] = 'Autoenrol cohort';
$string['auth_fieldlocks_help'] = ' ';

$string['auth_mainrule_fld'] = 'Main template. 1 template per line.';
$string['auth_secondrule_fld'] = 'Empty field text';
$string['auth_replace_arr'] = 'Replace array. 1 value per line, format: old_val|new_val';
$string['auth_delim'] = 'Delimiter';
$string['auth_delim_help'] = 'Different OS use different delimiters (end of line).<br>In Windows it`s usually CR+LF<br>In Linux - LF<br>etc.<br>If the module does not work, try to change this value.';

$string['auth_donttouchusers'] = 'Ignore users';
$string['auth_donttouchusers_help'] = 'Comma separated usernames.';
$string['auth_enableunenrol'] = 'Enable / Disable automatic unenrol';

$string['auth_tools_help'] = 'Unenrol function works only with cohorts associated with the module. With <a href="{$a->url}" target="_blank">this tool</a> you can convert / view / delete all cohorts you have.'; 

$string['cohorttoolmcae'] = 'Cohort operations';
