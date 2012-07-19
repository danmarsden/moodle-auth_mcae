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
require_once($CFG->dirroot . '/cohort/lib.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

$context = get_context_instance(CONTEXT_SYSTEM);
$returnurl = new moodle_url('/auth/mcae/convert.php');

admin_externalpage_setup('cohorttoolmcae');

require_capability('moodle/site:config', $context, $USER->id);

$action = optional_param('action', 'list', PARAM_ALPHA);
$clist = (isset($_POST['clist'])) ? $_POST['clist'] : false;

switch ($action) {
    case 'list':
        $cohorts = $DB->get_records('cohort', array('contextid'=>$context->id));
        $cohorts_list = array();

        foreach($cohorts as $cohort) {
            $cid = $cohort->id;
            $cname = format_string($cohort->name);
            $cohorts_list[$cid]['name'] = $cname;
            $cohorts_list[$cid]['component'] = $cohort->component;
            $cohorts_list[$cid]['count'] = $DB->count_records('cohort_members', array('cohortid'=>$cid));
        }

        $row = array();
        $cell = array();
        $rownum = 0;
        
        foreach($cohorts_list as $key => $val) {
            $color = ($val['component'] == 'auth_mcae') ? '#f4c430 !important' : '#e9967a !important';
            $viewurl = new moodle_url('/auth/mcae/view.php', array('cid' => $key));

            $row[$rownum] = new html_table_row();
            $cell[1] = new html_table_cell();
            $cell[2] = new html_table_cell();
            $cell[3] = new html_table_cell();
            $cell[4] = new html_table_cell();

            $cell[1]->text = '<input type="checkbox" name="clist[]" value="'.$key.'"> '.$val['name'];
            $cell[2]->text = $val['component'];
            $cell[3]->text = $val['count'];
            $cell[4]->text = '<a href="'.$viewurl.'">'.get_string('auth_userlink', 'auth_mcae').'</a>';

            $cell[1]->style = 'font-weight: bold; background-color: '. $color .';';
            $cell[2]->style = 'font-style: italic; background-color: '. $color .';';
            $cell[3]->style = 'background-color: '. $color .';';
            $cell[4]->style = 'background-color: '. $color .';';

            $row[$rownum]->cells = $cell;
            $rownum++;
        }

        $table = new html_table();
        $table->head = array(get_string('auth_cohortname', 'auth_mcae'),get_string('auth_component', 'auth_mcae'), get_string('auth_count', 'auth_mcae'), get_string('auth_link', 'auth_mcae'));
        $table->width = '60%';
        $table->data = $row;

        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('auth_cohorttoolmcae', 'auth_mcae'));
        
        echo get_string('auth_cohortoper_help', 'auth_mcae');
        echo "<form action=\"$returnurl\" method=\"POST\">";

        echo html_writer::table($table);

        echo '<select name="action"><option value="do">Convert to auth_mcae</option><option value="restore">Convert to manual</option><option value="delete">Delete cohorts</option></select>';
        echo '<input type="submit" name="submit" value="Submit">';
        echo '</form>';
    break;
    case 'do':
        if ($clist) {
            list($usql, $params) = $DB->get_in_or_equal($clist);
            $DB->set_field_select('cohort', 'component', 'auth_mcae', 'id ' . $usql, $params);
        };
        redirect($returnurl);
    break;
    case 'restore':
        if ($clist) {
            list($usql, $params) = $DB->get_in_or_equal($clist);
            $DB->set_field_select('cohort', 'component', '', 'id ' . $usql, $params);
        };
        redirect($returnurl);
    break;
    case 'delete':
        if ($clist) {
            set_time_limit(0);

            echo $OUTPUT->header();
            echo $OUTPUT->heading(get_string('auth_cohorttoolmcae', 'auth_mcae'));
            
            $progress = new progress_bar('delcohort');
            $progress->create();
            $delcount = count($clist);
            $delcurrent = 1;

            foreach ($clist as $cid){
                $cohort = new stdClass();
                $cohort->id = $cid;
                cohort_delete_cohort($cohort);
                $progress->update($delcurrent, $delcount, "$delcurrent / $delcount");
                $delcurrent++;
            };
        };
        echo $OUTPUT->continue_button($returnurl);
        echo $OUTPUT->footer();
        die();
    break;
}

echo $OUTPUT->footer();

?>
