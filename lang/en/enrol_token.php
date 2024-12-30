<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'enrol_token', language 'en'.
 *
 * @package    enrol_token
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['canntenrol'] = 'Enrolment is disabled or inactive';
$string['canntenrolearly'] = 'You cannot enrol yet; enrolment starts on {$a}.';
$string['canntenrollate'] = 'You cannot enrol any more, since enrolment ended on {$a}.';
$string['cohortnonmemberinfo'] = 'Only members of cohort \'{$a}\' can token-enrol.';
$string['cohortonly'] = 'Only cohort members';
$string['cohortonly_help'] = 'Token enrolment may be restricted to members of a specified cohort only. Note that changing this setting has no effect on existing enrolments.';
$string['confirmbulkdeleteenrolment'] = 'Are you sure you want to delete these user enrolments?';
$string['customwelcomemessage'] = 'Custom welcome message';
$string['customwelcomemessage_help'] = 'A custom welcome message may be added as plain text or Moodle-auto format, including HTML tags and multi-lang tags.

The following placeholders may be included in the message:

* Course name {$a->coursename}
* Link to user\'s profile page {$a->profileurl}
* User email {$a->email}
* User fullname {$a->fullname}';
$string['defaultrole'] = 'Default role assignment';
$string['defaultrole_desc'] = 'Select role which should be assigned to users during token enrolment';
$string['deleteselectedusers'] = 'Delete selected user enrolments';
$string['editselectedusers'] = 'Edit selected user enrolments';
$string['enrolenddate'] = 'End date';
$string['enrolenddate_help'] = 'If enabled, users can enrol themselves until this date only.';
$string['enrolenddaterror'] = 'Enrolment end date cannot be earlier than start date';
$string['enrolid'] = 'Enrol ID';
$string['enrolme'] = 'Enrol me';
$string['enrolperiod'] = 'Enrolment duration';
$string['enrolperiod_desc'] = 'Default length of time that the enrolment is valid. If set to zero, the enrolment duration will be unlimited by default.';
$string['enrolperiod_help'] = 'Length of time that the enrolment is valid, starting with the moment the user enrols themselves. If disabled, the enrolment duration will be unlimited.';
$string['enrolstartdate'] = 'Start date';
$string['enrolstartdate_help'] = 'If enabled, users can enrol themselves from this date onward only.';
$string['expiredaction'] = 'Enrolment expiry action';
$string['expiredaction_help'] = 'Select action to carry out when user enrolment expires. Please note that some user data and settings are purged from course during course unenrolment.';
$string['expirymessageenrolledbody'] = 'Dear {$a->user},

This is a notification that your enrolment in the course \'{$a->course}\' is due to expire on {$a->timeend}.

If you need help, please contact {$a->enroller}.';
$string['expirymessageenrolledsubject'] = 'Token enrolment expiry notification';
$string['expirymessageenrollerbody'] = 'Token enrolment in the course \'{$a->course}\' will expire within the next {$a->threshold} for the following users:

{$a->users}

To extend their enrolment, go to {$a->extendurl}';
$string['expirymessageenrollersubject'] = 'Token enrolment expiry notification';
$string['expirynotifyall'] = 'Teacher and enrolled user';
$string['expirynotifyenroller'] = 'Teacher only';
$string['generate'] = 'Generate';
$string['generatetokens'] = 'Generate tokens';
$string['invalidamount'] = 'Invalid amount of tokens to generate. The amount must be between 1 and 100';
$string['keyholder'] = 'You should have received this enrolment key from:';
$string['longtimenosee'] = 'Unenrol inactive after';
$string['longtimenosee_help'] = 'If users haven\'t accessed a course for a long time, then they are automatically unenrolled. This parameter specifies that time limit.';
$string['managetokens'] = 'Manage tokens';
$string['maxenrolled'] = 'Max enrolled users';
$string['maxenrolled_help'] = 'Specifies the maximum number of users that can token enrol. 0 means no limit.';
$string['maxenrolledreached'] = 'Maximum number of users allowed to token-enrol was already reached.';
$string['messageprovider:expiry_notification'] = 'Token enrolment expiry notifications';
$string['newenrols'] = 'Allow new token enrolments';
$string['newenrols_desc'] = 'Allow users to token enrol into new courses by default.';
$string['newenrols_help'] = 'This setting determines whether a user can enrol into this course.';
$string['pluginname'] = 'Token enrolment';
$string['pluginname_desc'] = 'The Token Enrollment plugin allows users to choose which courses they want to participate in and enroll using a token provided to them in advance.';
$string['privacy:metadata'] = 'The Token enrolment plugin does not store any personal data.';
$string['role'] = 'Default assigned role';
$string['sendcoursewelcomemessage'] = 'Send course welcome message';
$string['sendcoursewelcomemessage_help'] = 'When a user token enrols in the course, they may be sent a welcome message email. If sent from the course contact (by default the teacher), and more than one user has this role, the email is sent from the first user to be assigned the role.';
$string['sendexpirynotificationstask'] = "Token enrolment send expiry notifications task";
$string['status'] = 'Keep current token enrolments active';
$string['status_desc'] = 'Enable token enrolment method in new courses.';
$string['status_help'] = 'If set to No, any existing participants who enrolled themselves into the course will no longer have access.';
$string['syncenrolmentstask'] = 'Synchronise token enrolments task';
$string['timecreated'] = 'Time created';
$string['timeused'] = 'Time used';
$string['token'] = 'Enrolment token';
$string['token:config'] = 'Configure token enrol instances';
$string['token:enrolself'] = 'Self enrol in course';
$string['token:holdkey'] = 'Appear as the token enrolment key holder';
$string['token:manage'] = 'Manage enrolled users';
$string['token:unenrol'] = 'Unenrol users from course';
$string['token:unenrolself'] = 'Unenrol self from the course';
$string['tokendeleted'] = 'Token deleted';
$string['tokendeleteerror'] = 'Error deleting token, please try again';
$string['tokenid'] = 'Token ID';
$string['tokeninvalid'] = 'Incorrect enrolment token, please try again';
$string['tokenlength'] = 'Token length';
$string['tokenlength_help'] = 'The length of the token to generate';
$string['tokensgenerated'] = '{$a} tokens were generated';
$string['tokenusednotdelete'] = 'Token used, cannot be deleted';
$string['unenroltokenconfirm'] = 'Do you really want to unenrol yourself from course "{$a}"?';
$string['unenrolusers'] = 'Unenrol users';
$string['usedbynames'] = 'Used by';
$string['userid'] = 'User ID';
$string['welcometocourse'] = 'Welcome to {$a}';
$string['welcometocoursetext'] = 'Welcome to {$a->coursename}!

If you have not done so already, you should edit your profile page so that we can learn more about you:

  {$a->profileurl}';
