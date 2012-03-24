This Authentication plugin automatically enrol users into cohorts.

Cohort name depends on user profile field.

How to use:

1.    Copy plugin into /moodle/auth directory
2.    Go to Site administration - Notifications page and install it
3.    Go to Plugins - Authentication and enable plugin
4.    Configure main rules (template for cohort name)
    second rule (replace empty field)
    and replacement array
5.    ???
6.    PROFIT

Cohorts are created in CONTEXT_SYSTEM

EXAMPLE:
You have a custom profile fields "status" (student, teacher or admin) and "course". 

You wnat to enrol many users into cohorts like "course - status" than enrol cohorts into courses.

At configuration page set:
Main template to %profile_field_course - %profile_field_status (1 template per line, before profile fields type %)
Empty field text (When field is empty this value used) to none

Result:
When 1st course student logins, he enrol to cohort named "1 - student"
When 1st course teacher logins, he enrol to cohort named "1 - teacher"
When admin logins, he enrol to cohort named "none - admin" (Course not set, status - admin)

To rename "none - admin" cohort to "Administration" you must set a replacement array field at the configuration page (1 replacement per line, old_value|new_value)
In our case: none - admin|Administrator

Result:
When admin logins, he enrol to cohort named "Administrator"

-------
This plugin only create cohorts and enrol users to it.
To find the list of profile fields go to User - > User bulk operation and download 1-2 users.