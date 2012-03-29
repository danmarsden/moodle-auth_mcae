<?php

function xmldb_auth_mcae_upgrade($oldversion) {
    global $CFG, $DB, $OUTPUT;

    if ($oldversion < 2012032914) {
        $sql = "UPDATE {config_plugins} SET plugin = 'auth_mcae' WHERE plugin = 'auth/mcae'";
        $DB->execute($sql);

        echo $OUTPUT->notification('Update plugin configugation', 'notifysuccess');
    }

    return true;
}
