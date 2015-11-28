<?php

    function mcae_prepare_profile_data($data) {
        $reject = array('ajax_updatable_user_prefs', 'sesskey', 'preference', 'editing', 'access', 'message_lastpopup', 'enrol', '');
        if (is_array($data) or is_object($data)) {
            $new_data = array();
            foreach ($data as $key => $val) {
                if (!in_array($key, $reject)) {
                    $new_data[$key] = (is_array($val) or is_object($val)) ? mcae_prepare_profile_data($val) : format_string($val);
                }
            }
        } else {
            $new_data = format_string($data);
        }
        if (empty($new_data)) {
            return format_string('EMPTY');
        } else {
            return $new_data;
        }
    }

    function mcae_print_profile_data($data, $prefix = '', &$result) {
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                if (is_array($val)) {
                    $field = ($prefix == '') ? "$key" : "$prefix.$key";
                    mcae_print_profile_data($val, $field, $result);
                } else {
                    $field = ($prefix == '') ? "$key" : "$prefix.$key";
                    $title = format_string($val);
                    $result[] = "<span title=\"$title\">{{ $field }}</span>";
					//$result[] = "My $field is {{ $field }}";
                }
            }
        } else {
            $title = format_string($data);
            $result[] = "<span title=\"$title\">{{ $prefix }}</span>";
			//$result[] = "My $prefix is {{ $prefix }}";
        }
    }
