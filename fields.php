<?php

global $USER;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

$fldlist = array();
foreach ($USER as $key => $val){
    if (is_array($val)) {
        if (isset($val['text'])) {
            $fldlist[] = "<a href=\"#\" title=\"%$key\">%$key</a>";
        };
    } else {
        $fldlist[] = "<a href=\"#\" title=\"%$key\">%$key</a>";
    };
}

echo implode(', ', $fldlist);

?>

<script>
YUI().use('node', function (Y) {
    var copyPaste = function(e) {
        var tgt = e.currentTarget;
        txtarea.append(tgt.get('title'));
    }

    var txtarea = Y.one("textarea.pastehere");

    Y.all('p.amcopypaste a').on("click", copyPaste);

});
</script>
