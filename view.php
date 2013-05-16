<?php

/**
 * Autoenrol cohort authentication plugin version information
 *
 * @package    auth
 * @subpackage mcae
 * @copyright  2011 Andrew "Kama" (kamasutra12@yandex.ru) 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

admin_externalpage_setup('cohortviewmcae');

$context = get_context_instance(CONTEXT_SYSTEM);

require_capability('moodle/cohort:view', $context, $USER->id);

$cid = optional_param('cid', 0, PARAM_INT);

$cohorts = $DB->get_records('cohort', array('contextid'=>$context->id));
$select_options = '<option default value="0">'.get_string('auth_selectcohort', 'auth_mcae').'</option>';

foreach($cohorts as $cohort) {
    $cohortid = $cohort->id;
    $cohortname = format_string($cohort->name);
    $selected = ($cid == $cohortid) ? 'selected' : '';
    $select_options .= "<option $selected value=\"$cohortid\">$cohortname</option>";
}

$fullname = $DB->sql_fullname($first='firstname', $last='lastname');
$sql = "SELECT u.id AS uid, $fullname AS usrname FROM {cohort_members} AS cm JOIN {user} AS u ON u.id = cm.userid WHERE cm.cohortid = ? ORDER BY usrname";
$userlist = $DB->get_records_sql($sql, array($cid));
$total = 0;

$head = array(get_string('auth_username', 'auth_mcae'),get_string('auth_link', 'auth_mcae'));
$data = array();

if (empty($userlist)) {
    $data[] = array(get_string('auth_emptycohort', 'auth_mcae'),'');
} else {
    foreach ($userlist as $user) {
        $link = new moodle_url('/user/profile.php', array('id' => $user->uid));
        $data[] = array($user->usrname, '<a href="'.$link.'">'.get_string('auth_userprofile', 'auth_mcae').'</a>');
        $total++;
    };
    $data[] = array(get_string('auth_total', 'auth_mcae'), $total);
};

$table = new html_table();
$table->head = $head;
$table->width = '60%';
$table->data = $data;

$return = new moodle_url('/auth/mcae/view.php');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('auth_viewcohort', 'auth_mcae'));

echo '<form action="'.$return.'" method="POST"><select name="cid">';
echo $select_options;
echo '<input type="submit"></form><br />';

echo '<br>';
echo html_writer::table($table);

echo $OUTPUT->footer();
?>
