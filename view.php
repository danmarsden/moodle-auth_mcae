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

require_login();

$context = get_context_instance(CONTEXT_SYSTEM);

require_capability('moodle/cohort:view', $context, $USER->id);

$cid = required_param('cid', PARAM_INT);

$sql = "SELECT u.id AS uid, CONCAT(u.firstname, ' ', u.lastname) AS usrname FROM {cohort_members} AS cm JOIN {user} AS u ON u.id = cm.userid WHERE cm.cohortid = ? ORDER BY usrname";
$userlist = $DB->get_record_sql($sql, array($cid));
$total = 0;

$head = array('User name','Link');
$data = array();

if (empty($userlist)) {
    $data[] = array('Cohort empty','');
} else if (count($userlist) == 1) {
    $link = new moodle_url('/user/profile.php', array('id' => $userlist->uid));
    $data[] = array($userlist->usrname, '<a target="_blank" href="'.$link.'">User profile &gt;&gt;</a>');
    $total++;
} else {
    foreach ($userlist as $user) {
        $link = new moodle_url('', array('id' => $userlist->uid));
        $data[] = array($user['usrname'], '<a href="'.$link.'">User profile &gt;&gt;</a>');
        $total++;
    };
};

$table = new html_table();
$table->head = $head;
$table->width = '60%';
$table->data = $data;
//$output .= '<tr><td align="right"><b>Total</b></td><td>'.$total.'</td></tr>';

$PAGE->set_pagelayout('report');
$PAGE->set_context($context);
$PAGE->set_url('/auth/mcae/view.php', array('cid'=>$cid));
$PAGE->set_title('Cohort view');
//$PAGE->set_heading('Cohort view');

$return = new moodle_url('/auth/mcae/convert.php');

echo $OUTPUT->header();
echo $OUTPUT->heading('Cohort view');

echo $OUTPUT->continue_button($return);

echo '<br>';
echo html_writer::table($table);

echo $OUTPUT->continue_button($return);

echo $OUTPUT->footer();
?>
