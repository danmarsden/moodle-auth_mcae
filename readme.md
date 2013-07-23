# Autoenrol cohort authentication plugin for moodle 2.x

This authentication plugin automatically enrol users into cohorts.

Cohort name depends on user profile field.

Cohorts are created in CONTEXT_SYSTEM.

## Installation

 * Download the archive and extract the files, or clone the repository from GitHub
 * Copy the 'mcae' folder into your_moodle/auth
 * Visit Site administration - Notifications page and follow the instructions

If you use an Email based self registration or similar plugin and users enrolls into cohort after second login copy/paste this code into moodle/themes/your_theme/layout/general.php (or default.php)

> **NOTE:** Enable and configure mcae plugin first!


    <?php

    global $SESSION, $USER;

    if ($USER->id != 0) { // Only for autenticated users
        $mcae = get_auth_plugin('mcae'); //Get mcae plugin

        if (isset($SESSION->mcautoenrolled)) {
            if (!$SESSION->mcautoenrolled) {
                $mcae->user_authenticated_hook($USER,$USER->username,""); //Autoenrol if mcautoenrolled FALSE
            }
        } else {
            $mcae->user_authenticated_hook($USER,$USER->username,""); //Autoenrol if mcautoenrolled NOT SET
        }
    }

    ?>

## Upgrade

 * Replace the your_moodle/auth/mcae folder with new one
 * Visit Site administration - Notifications page and follow the instructions

## Configuration

**Template for cohort name**

1 template per line.

In the template you may use any characters (except '%') and profile field values. To insert a profile field value, use '%' sign and name of the field (%lastname, %firstname, etc).
Custom profile fields have two templates: %profile_field_name and %profile_field_raw_name. It's useful with fields like 'menu of choices':
 * %profile_field_raw_name - number of the selected value
 * %profile_field_name - selected value

An email field have 3 variants:
 * %email - full email
 * %email_username - only username
 * %email_domain - only domain

> **Note:** Profile field templates is case sensitive. %username and %UserName are two different fields!

> Custom profile field types:

> 'Textarea' type have only 'raw' variant. All HTML tags is removed.

> 'Text input' returns the same value in both variants

> Checkboxes returns 1 or 0 in both variants.

> Date/time fields returns unix timestamp

**Split arguments:**
Synopsis: %split(fieldname|delimiter)

Returns multiple cohorts, each of which is formed by splitting field on boundaries formed by the delimiter.

Arguments:
 * fieldname - Profile field name with '%' sign.
 * delimiter - The boundary string. 1 - 5 signs.

> **Example:**

> User John set custom profile field "Known languages" to "English, Spanish, Chinese"

> Main template contains string "Language - %split(%knownlanguage|, )"

> John will be enrolled in 3 cohorts: Language - English, Language - Spanish and Language - Chinese


**Replace empty field**

If profile field is empty then it's replaced with this value.

**Replacement array**

You can change the cohort name after it's generation.

1 replacement per line, format - old value|new value

    very long cohort name|shortname

**Unenrol**

Unenrol users from cohorts after profile change.

To use an unenrol feature:

 * Go to Plugins - Authentication - Autoenrol cohort and enable unenrol function
 * Go to yourmoodle/auth/mcae/convert.php and convert cohorts you want to "auth_mcae".

Convert only cohorts that are created by the "auth_mcae" module!

At yourmoodle/auth/mcae/convert.php page you may view, delete or convert cohorts into "manual" or "auth_mcae" mode.

**Ignore users**

List of users to ignore. Comma separated usernames.

    admin,test,manager,teacher1,teacher2

## Usage example

You have a custom profile fields "status" (student, teacher or admin) and "course". 

You wnat to enrol many users into cohorts like "course - status" than enrol cohorts into courses.

At configuration page set:

Main template to %profile_field_course - %profile_field_status

Empty field text to 'none'

**Result:**

 * When 1st course student logins, he enrol to cohort named "1 - student"
 * When 1st course teacher logins, he enrol to cohort named "1 - teacher"
 * When admin logins, he enrol to cohort named "none - admin" (Course not set, status - admin)

To rename "none - admin" cohort to "Administration" you must set a replacement array field at the configuration page
In our case: none - admin|Administrator

**Result:**

When admin logins, he enrol to cohort named "Administrator"
