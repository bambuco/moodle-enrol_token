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
 * Token enrolment plugin - support for user token unenrolment.
 *
 * @package    enrol_token
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

$enrolid = required_param('enrolid', PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

$instance = $DB->get_record('enrol', ['id'=>$enrolid, 'enrol'=>'token'], '*', MUST_EXIST);
$course = $DB->get_record('course', ['id'=>$instance->courseid], '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

require_login();
if (!is_enrolled($context)) {
    redirect(new moodle_url('/'));
}
require_login($course);

$plugin = enrol_get_plugin('token');

// Security defined inside following function.
if (!$plugin->get_unenrolself_link($instance)) {
    redirect(new moodle_url('/course/view.php', ['id'=>$course->id]));
}

$PAGE->set_url('/enrol/token/unenrolself.php', ['enrolid' => $instance->id]);
$PAGE->set_title($plugin->get_instance_name($instance));

if ($confirm and confirm_sesskey()) {
    $plugin->unenrol_user($instance, $USER->id);

    \core\notification::success(get_string('youunenrolledfromcourse', 'enrol', format_string($course->fullname, true,
        ["context" => $context])));

    redirect(new moodle_url('/index.php'));
}

echo $OUTPUT->header();
$yesurl = new moodle_url($PAGE->url, ['confirm'=>1, 'sesskey'=>sesskey()]);
$nourl = new moodle_url('/course/view.php', array('id'=>$course->id));
$message = get_string('unenroltokenconfirm', 'enrol_token', format_string($course->fullname));
echo $OUTPUT->confirm($message, $yesurl, $nourl);
echo $OUTPUT->footer();
