<?php

require_once('../../config.php');

require_login();

$context = get_context_instance(CONTEXT_SYSTEM);

require_capability('moodle/site:config', $context->id, $USER->id);

$action = optional_param('action', 'list', PARAM_ALPHA);
$clist = optional_param('clist', '', PARAM_ALPHA);

$cohorts = $DB->get_records('cohort', array('contextid'=>$context->id, 'component'=>''));
$cohorts_list = array();

foreach($cohorts as $cohort) {
    $cid = $cohort->id;
    $cname = format_string($cohort->name);
    $cohorts_list[$cid] = $cname;
}

$output = '<html><head><title>Cohort converter</title></head><body>';

switch ($action) {
    case 'list':
        $row = array();
        $returnurl = new moodle_url('/auth/mcae/convert.php');
        
        foreach($cohorts_list as $key => $val) {
            $row[] = "<input type=\"checkbox\" checked name=\"clist[]\" value=\"$key\"> $val<br />";
        }
        
        $output .= '<h2>Cohort converter</h2><br>';
        $output .= '<p>Select cohorts you want to convert.</p>';
        $output .= '<p><b>NOTE:</b> <i>You <b>unable</b> to edit converted cohorts manually!</i></p>';
        $output .= '<h2>Backup your database!!!</h2><p> </p>';
        $output .= "<form action=\"$returnurl\" method=\"POST\">";
        $output .= implode('',$row);
        $output .= '<input type="submit" name="submit" value="Submit">';
        $output .= '</form>';
    break;
    case 'do':
        $output .= print_r($clist, TRUE);
    break;
}
$output .= '</body></html>';

print $output;

?>