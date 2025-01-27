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
 * Manage tokens.
 *
 * @package    enrol_token
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_reportbuilder\system_report_factory;

require('../../config.php');
require_once($CFG->dirroot . '/enrol/token/locallib.php');

$enrolid = required_param('enrolid', PARAM_INT);
$delete = optional_param('delete', 0, PARAM_INT);
$amount = optional_param('amount', 0, PARAM_INT);

$instance = $DB->get_record('enrol', ['id' => $enrolid, 'enrol' => 'token'], '*', MUST_EXIST);
$course = $DB->get_record('course', ['id' => $instance->courseid], '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

require_login($course);
require_capability('enrol/token:config', $context);

if (!$enroltoken = enrol_get_plugin('token')) {
    throw new coding_exception('Can not instantiate enrol_token');
}

$url = new moodle_url('/enrol/token/tokens.php', ['enrolid' => $instance->id]);
$title = get_string('managetokens', 'enrol_token');

// Delete a token, don't require confirmation because is a random information.
if ($delete && confirm_sesskey()) {
    $token = $DB->get_record('enrol_token_tokens', ['id' => $delete], '*', MUST_EXIST);

    if ($token->userid) {
        throw new \moodle_exception('tokenusednotdelete', 'enrol_token');
    }

    if ($DB->delete_records('enrol_token_tokens', ['id' => $token->id])) {
        redirect($url, get_string('tokendeleted', 'enrol_token'), null, \core\output\notification::NOTIFY_SUCCESS);
    } else {
        redirect($url, get_string('tokendeleteerror', 'enrol_token'), null, \core\output\notification::NOTIFY_ERROR);
    }
}

if ($amount) {
    if ($amount >= 1 && $amount <= 100) {
        $enroltoken->generate_tokens($instance, $amount);
        redirect($url, get_string('tokensgenerated', 'enrol_token', $amount), null, \core\output\notification::NOTIFY_SUCCESS);
    } else {
        redirect($url, get_string('invalidamount', 'enrol_token'), null, \core\output\notification::NOTIFY_ERROR);
    }
    exit;
}

$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$PAGE->set_title($title);
$PAGE->set_heading($course->fullname);
navigation_node::override_active_url(new moodle_url('/enrol/instances.php', ['id' => $course->id]));
$PAGE->navbar->add($title, $url);

echo $OUTPUT->header();
echo $OUTPUT->heading($title);

// List the tokens.
$report = system_report_factory::create(\enrol_token\systemreports\tokens::class,
                                            $context, '', '', 0, ['enrolid' => $instance->id]);

echo $report->output();

$renderable = new \enrol_token\output\generatetokens($instance);
$renderer = $PAGE->get_renderer('enrol_token');
echo $renderer->render($renderable);

echo $OUTPUT->footer();
